<?php
$user = currentUser();
$userId = currentUserId();

require_once __DIR__ . '/../includes/analytics.php';

$totalWorkouts = dbFetchOne(
    "SELECT COUNT(*) as count FROM workouts WHERE user_id = ? AND ended_at IS NOT NULL",
    [$userId]
)['count'] ?? 0;

$totalVolume = dbFetchOne(
    "SELECT SUM(ws.weight * ws.reps) as total FROM workout_sets ws JOIN workouts w ON ws.workout_id = w.id WHERE w.user_id = ? AND w.ended_at IS NOT NULL",
    [$userId]
)['total'] ?? 0;

$totalSets = dbFetchOne(
    "SELECT COUNT(*) as count FROM workout_sets ws JOIN workouts w ON ws.workout_id = w.id WHERE w.user_id = ? AND w.ended_at IS NOT NULL AND ws.completed_at IS NOT NULL",
    [$userId]
)['count'] ?? 0;

$weekStart = strtotime('monday this week') * 1000;
$workoutsThisWeek = dbFetchOne(
    "SELECT COUNT(*) as count FROM workouts WHERE user_id = ? AND ended_at IS NOT NULL AND started_at >= ?",
    [$userId, $weekStart]
)['count'] ?? 0;

$activeWorkout = dbFetchOne(
    "SELECT id, started_at, notes FROM workouts WHERE user_id = ? AND ended_at IS NULL",
    [$userId]
);

$recentWorkouts = dbFetchAll(
    "SELECT id, started_at, ended_at, notes FROM workouts WHERE user_id = ? AND ended_at IS NOT NULL ORDER BY started_at DESC LIMIT 5",
    [$userId]
);

$today = strtotime('today') * 1000;
$tomorrow = strtotime('tomorrow') * 1000;
$todayCardio = dbFetchAll(
    "SELECT * FROM cardio_sessions WHERE user_id = ? AND completed_at >= ? AND completed_at < ? ORDER BY completed_at ASC",
    [$userId, $today, $tomorrow]
);
$amDone = false;
$pmDone = false;
foreach ($todayCardio as $cardio) {
    $hour = date('G', $cardio['completed_at'] / 1000);
    if ($hour < 12) $amDone = true;
    else $pmDone = true;
}

// New analytics
$monthlyStats = getMonthlyStats($userId, 6);
$streak = getWorkoutStreak($userId);
$weeklyConsistency = getWeeklyConsistency($userId);
$bodyComposition = getBodyCompositionHistory($userId);
$workoutTemplates = getWorkoutTemplates($userId);

renderPage('Dashboard', function() use ($user, $totalWorkouts, $totalVolume, $totalSets, $workoutsThisWeek, $activeWorkout, $recentWorkouts, $amDone, $pmDone, $monthlyStats, $streak, $weeklyConsistency, $bodyComposition, $workoutTemplates) {
    ?>
    <div class="welcome-banner">Welcome back!</div>
    <h1>Dashboard</h1>
    
    <?php if ($activeWorkout): ?>
        <div class="workout-in-progress-card">
            <div class="workout-in-progress-header">
                <span class="workout-in-progress-title">Workout In Progress</span>
                <span class="workout-in-progress-time"><?= timeAgo((int)($activeWorkout['started_at'] / 1000)) ?></span>
            </div>
            <a href="/workouts/log" class="btn btn-primary btn-chevron">CONTINUE WORKOUT</a>
        </div>
    <?php endif; ?>
    
    <!-- Streak Widget -->
    <div class="dashboard-widget streak-widget" style="background: linear-gradient(135deg, var(--surface) 0%, var(--surface-elevated) 100%); border: 1px solid var(--border); border-radius: 12px; padding: 16px; margin-bottom: 20px;">
        <div style="display: flex; justify-content: space-between; align-items: center;">
            <div>
                <div style="font-size: 12px; color: var(--text-dim); text-transform: uppercase; letter-spacing: 1px; margin-bottom: 4px;">Current Streak</div>
                <div style="font-size: 32px; font-weight: 700; color: var(--accent);">
                    <?= $streak['current'] ?> 
                    <span style="font-size: 14px; color: var(--text-dim); font-weight: 400;">day<?= $streak['current'] !== 1 ? 's' : '' ?></span>
                </div>
            </div>
            <div style="text-align: right;">
                <div style="font-size: 12px; color: var(--text-dim); margin-bottom: 4px;">Longest</div>
                <div style="font-size: 18px; font-weight: 700;"><?= $streak['longest'] ?> days</div>
                <?php if ($streak['last_workout']): ?>
                    <div style="font-size: 11px; color: var(--text-dim);">Last: <?= date('M j', strtotime($streak['last_workout'])) ?></div>
                <?php endif; ?>
            </div>
        </div>
        <?php if (!$streak['active'] && $streak['current'] === 0): ?>
            <div style="margin-top: 12px; padding-top: 12px; border-top: 1px solid var(--border);">
                <div style="font-size: 12px; color: var(--warning);">⚡ Start a workout today to begin a new streak!</div>
            </div>
        <?php endif; ?>
    </div>
    
    <!-- Weekly Consistency Heatmap -->
    <div class="dashboard-widget" style="background: var(--surface); border: 1px solid var(--border); border-radius: 12px; padding: 16px; margin-bottom: 20px;">
        <div style="font-size: 12px; color: var(--text-dim); text-transform: uppercase; letter-spacing: 1px; margin-bottom: 12px;">Last 12 Weeks</div>
        <div style="display: flex; gap: 4px; align-items: flex-end; height: 60px;">
            <?php foreach ($weeklyConsistency as $week): 
                $height = min(100, ($week['workouts'] / 5) * 100);
                $color = $week['workouts'] >= 3 ? 'var(--success)' : ($week['workouts'] > 0 ? 'var(--accent)' : 'var(--border)');
            ?>
                <div style="flex: 1; display: flex; flex-direction: column; align-items: center; gap: 4px;">
                    <div style="width: 100%; height: <?= $height ?>%; background: <?= $color ?>; border-radius: 3px; min-height: 4px; transition: all 0.2s;" title="<?= $week['week'] ?>: <?= $week['workouts'] ?> workouts"></div>
                    <div style="font-size: 9px; color: var(--text-dim); transform: rotate(-45deg); transform-origin: top left; white-space: nowrap;"><?= $week['week'] ?></div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
    
    <!-- Monthly Volume Chart -->
    <?php if (!empty($monthlyStats)): ?>
    <div class="dashboard-widget" style="background: var(--surface); border: 1px solid var(--border); border-radius: 12px; padding: 16px; margin-bottom: 20px;">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 12px;">
            <div style="font-size: 12px; color: var(--text-dim); text-transform: uppercase; letter-spacing: 1px;">Monthly Volume</div>
            <div style="font-size: 11px; color: var(--text-dim);">Last 6 months</div>
        </div>
        <div style="display: flex; gap: 8px; align-items: flex-end; height: 100px; padding-bottom: 24px; position: relative;">
            <?php 
                $maxVolume = max(array_column($monthlyStats, 'volume')) ?: 1;
                foreach ($monthlyStats as $stat): 
                    $height = ($stat['volume'] / $maxVolume) * 80;
            ?>
                <div style="flex: 1; display: flex; flex-direction: column; align-items: center; gap: 6px;">
                    <div style="font-size: 10px; color: var(--text-dim);"><?= number_format($stat['volume'] / 1000, 1) ?>k</div>
                    <div style="width: 100%; height: <?= $height ?>px; background: linear-gradient(to top, var(--accent), var(--accent-hover)); border-radius: 4px 4px 0 0; min-height: 4px;"></div>
                    <div style="font-size: 10px; color: var(--text); font-weight: 600;"><?= $stat['workouts'] ?></div>
                    <div style="font-size: 9px; color: var(--text-dim);"><?= explode(' ', $stat['month_label'])[0] ?></div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
    <?php endif; ?>
    
    <!-- Body Composition Widget -->
    <?php if (!empty($bodyComposition)): 
        $latest = end($bodyComposition);
        $first = $bodyComposition[0];
        $weightChange = $latest['weight'] - $first['weight'];
        $bodyFatChange = $latest['body_fat'] - $first['body_fat'];
    ?>
    <div class="dashboard-widget" style="background: var(--surface); border: 1px solid var(--border); border-radius: 12px; padding: 16px; margin-bottom: 20px;">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 12px;">
            <div style="font-size: 12px; color: var(--text-dim); text-transform: uppercase; letter-spacing: 1px;">Body Composition</div>
            <div style="font-size: 11px; color: var(--text-dim);">Last scan: <?= date('M j', strtotime($latest['scan_date'])) ?></div>
        </div>
        <div style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 12px;">
            <div style="text-align: center;">
                <div style="font-size: 20px; font-weight: 700;"><?= number_format($latest['weight'], 1) ?></div>
                <div style="font-size: 10px; color: var(--text-dim);">lbs</div>
                <?php if (count($bodyComposition) > 1): ?>
                    <div style="font-size: 11px; color: <?= $weightChange < 0 ? 'var(--success)' : ($weightChange > 0 ? 'var(--error)' : 'var(--text-dim)') ?>;">
                        <?= $weightChange > 0 ? '+' : '' ?><?= number_format($weightChange, 1) ?>
                    </div>
                <?php endif; ?>
            </div>
            <div style="text-align: center;">
                <div style="font-size: 20px; font-weight: 700;"><?= $latest['body_fat'] ?>%</div>
                <div style="font-size: 10px; color: var(--text-dim);">Body Fat</div>
                <?php if (count($bodyComposition) > 1): ?>
                    <div style="font-size: 11px; color: <?= $bodyFatChange < 0 ? 'var(--success)' : ($bodyFatChange > 0 ? 'var(--error)' : 'var(--text-dim)') ?>;">
                        <?= $bodyFatChange > 0 ? '+' : '' ?><?= number_format($bodyFatChange, 1) ?>%
                    </div>
                <?php endif; ?>
            </div>
            <div style="text-align: center;">
                <div style="font-size: 20px; font-weight: 700;"><?= number_format($latest['muscle_mass'], 1) ?></div>
                <div style="font-size: 10px; color: var(--text-dim);">Muscle lbs</div>
                <?php if (count($bodyComposition) > 1):
                    $muscleChange = $latest['muscle_mass'] - $first['muscle_mass'];
                ?>
                    <div style="font-size: 11px; color: <?= $muscleChange > 0 ? 'var(--success)' : ($muscleChange < 0 ? 'var(--error)' : 'var(--text-dim)') ?>;">
                        <?= $muscleChange > 0 ? '+' : '' ?><?= number_format($muscleChange, 1) ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
    <?php endif; ?>
    
    <!-- Quick Start Templates -->
    <?php if (!empty($workoutTemplates)): ?>
    <div class="dashboard-widget" style="background: var(--surface); border: 1px solid var(--border); border-radius: 12px; padding: 16px; margin-bottom: 20px;">
        <div style="font-size: 12px; color: var(--text-dim); text-transform: uppercase; letter-spacing: 1px; margin-bottom: 12px;">Quick Start</div>
        <div style="display: flex; flex-wrap: wrap; gap: 8px;">
            <?php foreach (array_slice($workoutTemplates, 0, 5) as $template): ?>
                <form method="POST" action="/action/workouts/start" style="display: inline;">
                    <?= csrfField() ?>
                    <input type="hidden" name="workout_name" value="<?= htmlspecialchars($template['template_name']) ?>">
                    <button type="submit" class="btn btn-small" style="width: auto; background: var(--surface-elevated); border: 1px solid var(--border); font-size: 12px;">
                        <?= htmlspecialchars($template['template_name']) ?> (<?= $template['usage_count'] ?>)
                    </button>
                </form>
            <?php endforeach; ?>
        </div>
    </div>
    <?php endif; ?>
    
    <div class="stats-grid">
        <div class="stat-box"><div class="stat-value"><?= $totalWorkouts ?></div><div class="stat-label">Workouts</div></div>
        <div class="stat-box"><div class="stat-value"><?= number_format($totalVolume / 1000, 1) ?>k</div><div class="stat-label">Lbs Volume</div></div>
        <div class="stat-box"><div class="stat-value"><?= $totalSets ?></div><div class="stat-label">Total Sets</div></div>
        <div class="stat-box"><div class="stat-value"><?= $workoutsThisWeek ?></div><div class="stat-label">This Week</div></div>
    </div>
    
    <a href="/cardio" style="text-decoration:none;color:inherit;">
        <div style="background:var(--bg-card);border:1px solid var(--border);border-radius:12px;padding:16px;margin-bottom:24px;display:flex;justify-content:space-between;align-items:center;">
            <div><div style="font-weight:700;margin-bottom:4px;">🚴 Today's Cardio</div><div style="font-size:14px;color:var(--text-dim);">15 min AM + 15 min PM</div></div>
            <div style="display:flex;gap:12px;">
                <div style="text-align:center;"><div style="font-size:12px;color:var(--text-dim);margin-bottom:4px;">AM</div><div style="width:32px;height:32px;border-radius:50%;background:<?= $amDone ? 'var(--success)' : 'var(--border)' ?>;display:flex;align-items:center;justify-content:center;color:<?= $amDone ? 'var(--bg)' : 'var(--text-dim)' ?>;font-weight:700;"><?= $amDone ? '✓' : '' ?></div></div>
                <div style="text-align:center;"><div style="font-size:12px;color:var(--text-dim);margin-bottom:4px;">PM</div><div style="width:32px;height:32px;border-radius:50%;background:<?= $pmDone ? 'var(--success)' : 'var(--border)' ?>;display:flex;align-items:center;justify-content:center;color:<?= $pmDone ? 'var(--bg)' : 'var(--text-dim)' ?>;font-weight:700;"><?= $pmDone ? '✓' : '' ?></div></div>
            </div>
        </div>
    </a>
    
    <a href="/routines" class="btn btn-primary btn-chevron" style="margin-bottom:24px;">START ROUTINE</a>
    
    <?php if (!empty($recentWorkouts)): ?>
        <div class="recent-workouts-title">RECENT WORKOUTS</div>
        <div class="card" style="padding:0;">
            <?php foreach ($recentWorkouts as $workout): 
                $workoutName = $workout['notes'] ?: 'Freestyle Workout';
            ?>
                <a href="/workouts/view?id=<?= $workout['id'] ?>" class="recent-workout-item" style="text-decoration:none;color:inherit;padding:14px 16px;">
                    <div class="recent-workout-info">
                        <div class="recent-workout-date"><?= formatDate((int)$workout['started_at']) ?> - <?= htmlspecialchars($workoutName) ?></div>
                    </div>
                    <span class="recent-workout-chevron">›</span>
                </a>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
    
    <form method="POST" action="/action/auth/logout" style="margin-top:40px;"><?= csrfField() ?><button type="submit" class="btn btn-small" style="width:auto;">Logout</button></form>
    <?php
});
