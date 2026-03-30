<?php
/**
 * Stats Page - Enhanced with 1RM estimates and trends
 */

require_once __DIR__ . '/../includes/analytics.php';

$userId = currentUserId();

// Overall stats
$totalWorkouts = dbFetchOne(
    "SELECT COUNT(*) as count FROM workouts WHERE user_id = ? AND ended_at IS NOT NULL",
    [$userId]
)['count'] ?? 0;

$totalVolume = dbFetchOne(
    "SELECT SUM(ws.weight * ws.reps) as total 
     FROM workout_sets ws 
     JOIN workouts w ON ws.workout_id = w.id 
     WHERE w.user_id = ? AND w.ended_at IS NOT NULL AND ws.completed_at IS NOT NULL",
    [$userId]
)['total'] ?? 0;

$totalSets = dbFetchOne(
    "SELECT COUNT(*) as count 
     FROM workout_sets ws 
     JOIN workouts w ON ws.workout_id = w.id 
     WHERE w.user_id = ? AND w.ended_at IS NOT NULL AND ws.completed_at IS NOT NULL",
    [$userId]
)['count'] ?? 0;

// This week's workouts
$weekAgo = (time() - 7 * 86400) * 1000;
$thisWeekWorkouts = dbFetchOne(
    "SELECT COUNT(*) as count FROM workouts WHERE user_id = ? AND ended_at IS NOT NULL AND started_at >= ?",
    [$userId, $weekAgo]
)['count'] ?? 0;

// Exercise stats
$exerciseStats = dbFetchAll(
    "SELECT 
        e.name,
        e.id as exercise_id,
        COUNT(*) as set_count,
        MAX(ws.weight) as max_weight,
        SUM(ws.weight * ws.reps) as total_volume
     FROM workout_sets ws 
     JOIN exercises e ON ws.exercise_id = e.id 
     JOIN workouts w ON ws.workout_id = w.id 
     WHERE w.user_id = ? AND w.ended_at IS NOT NULL AND ws.completed_at IS NOT NULL
     GROUP BY e.id, e.name
     ORDER BY total_volume DESC 
     LIMIT 10",
    [$userId]
);

// Get 1RM estimates for top lifts
$oneRMData = [];
foreach ($exerciseStats as $stat) {
    $bestSet = dbFetchOne(
        "SELECT weight, reps 
         FROM workout_sets ws
         JOIN workouts w ON ws.workout_id = w.id
         WHERE w.user_id = ? AND ws.exercise_id = ? AND ws.completed_at IS NOT NULL
         ORDER BY (weight * reps) DESC
         LIMIT 1",
        [$userId, $stat['exercise_id']]
    );
    
    if ($bestSet && $bestSet['reps'] <= 12) {
        $oneRM = calculateOneRM((float)$bestSet['weight'], (int)$bestSet['reps']);
        if ($oneRM) {
            $oneRMData[] = [
                'exercise' => $stat['name'],
                'weight' => $bestSet['weight'],
                'reps' => $bestSet['reps'],
                'estimated_1rm' => $oneRM
            ];
        }
    }
}

// Monthly trends
$monthlyStats = getMonthlyStats($userId, 6);

renderPage('Statistics', function() use ($totalWorkouts, $totalVolume, $totalSets, $thisWeekWorkouts, $exerciseStats, $oneRMData, $monthlyStats) {
    ?>
    <h1>STATISTICS</h1>
    
    <!-- Stats Grid -->
    <div class="stats-grid">
        <div class="stat-box">
            <div class="stat-value"><?= $totalWorkouts ?></div>
            <div class="stat-label">WORKOUTS</div>
        </div>
        <div class="stat-box">
            <div class="stat-value"><?= number_format($totalVolume / 1000, 1) ?>k</div>
            <div class="stat-label">LBS VOLUME</div>
        </div>
        <div class="stat-box">
            <div class="stat-value"><?= $totalSets ?></div>
            <div class="stat-label">TOTAL SETS</div>
        </div>
        <div class="stat-box">
            <div class="stat-value"><?= $thisWeekWorkouts ?></div>
            <div class="stat-label">THIS WEEK</div>
        </div>
    </div>
    
    <!-- Monthly Volume Trend -->
    <?php if (!empty($monthlyStats)): ?>
    <div class="stats-graph-card">
        <div class="stats-graph-title">Monthly Volume Trend</div>
        <div class="stats-graph-container">
            <?php 
                $maxVolume = max(array_column($monthlyStats, 'volume')) ?: 1;
                foreach ($monthlyStats as $stat): 
                    $heightPercent = ($stat['volume'] / $maxVolume) * 100;
                    $barHeight = max(4, min(100, $heightPercent));
            ?>
                <div class="stats-graph-bar-wrapper">
                    <div class="stats-graph-value"><?= number_format($stat['volume'] / 1000, 0) ?>k</div>
                    <div class="stats-graph-bar" style="height: <?= $barHeight ?>%;"></div>
                    <div class="stats-graph-count"><?= $stat['workouts'] ?></div>
                    <div class="stats-graph-month"><?= $stat['month_label'] ?></div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
    <?php endif; ?>
    
    <!-- 1RM Estimates -->
    <?php if (!empty($oneRMData)): ?>
    <div class="stats-section-title" style="margin-top: 32px;">ESTIMATED 1 REP MAX</div>
    <div class="card" style="padding: 0; overflow: hidden;">
        <?php foreach (array_slice($oneRMData, 0, 5) as $pr): ?>
            <div style="display: flex; justify-content: space-between; align-items: center; padding: 14px 16px; border-bottom: 1px solid var(--border);">
                <div>
                    <div style="font-weight: 600;"><?= e($pr['exercise']) ?></div>
                    <div style="font-size: 12px; color: var(--text-dim);">Best: <?= $pr['weight'] ?> × <?= $pr['reps'] ?></div>
                </div>
                <div style="text-align: right;">
                    <div style="font-size: 20px; font-weight: 700; color: var(--accent);"><?= $pr['estimated_1rm'] ?> <span style="font-size: 12px; color: var(--text-dim);">lbs</span></div>
                    <div style="font-size: 10px; color: var(--text-dim);">Est. 1RM</div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
    <?php endif; ?>
    
    <!-- Top Exercises -->
    <div class="stats-section-title" style="margin-top: 32px;">TOP EXERCISES BY VOLUME</div>
    
    <?php if (empty($exerciseStats)): ?>
        <div class="empty">
            <p>No exercise data yet</p>
        </div>
    <?php else: ?>
        <div class="exercise-stat-list">
            <?php foreach ($exerciseStats as $stat): ?>
                <div class="exercise-stat-item">
                    <div class="exercise-stat-name"><?= e($stat['name']) ?></div>
                    <div class="exercise-stat-value"><?= number_format($stat['total_volume'] / 1000, 1) ?>k</div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
    
    <!-- Export Link -->
    <div style="margin-top: 32px; text-align: center;">
        <a href="/export" class="btn btn-small" style="width: auto; display: inline-flex; align-items: center; gap: 8px;">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/>
                <polyline points="7 10 12 15 17 10"/>
                <line x1="12" y1="15" x2="12" y2="3"/>
            </svg>
            Export Your Data
        </a>
    </div>
    <?php
});
