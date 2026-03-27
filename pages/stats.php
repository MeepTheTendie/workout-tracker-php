<?php
/**
 * Stats Page - Redesigned
 */

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
        COUNT(*) as set_count,
        MAX(ws.weight) as max_weight,
        SUM(ws.weight * ws.reps) as total_volume
     FROM workout_sets ws 
     JOIN exercises e ON ws.exercise_id = e.id 
     JOIN workouts w ON ws.workout_id = w.id 
     WHERE w.user_id = ? AND w.ended_at IS NOT NULL AND ws.completed_at IS NOT NULL
     GROUP BY e.id 
     ORDER BY total_volume DESC 
     LIMIT 10",
    [$userId]
);

renderPage('Statistics', function() use ($totalWorkouts, $totalVolume, $totalSets, $thisWeekWorkouts, $exerciseStats) {
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
    
    <!-- Top Exercises -->
    <div class="stats-section-title">TOP EXERCISES BY VOLUME</div>
    
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
    <?php
});
