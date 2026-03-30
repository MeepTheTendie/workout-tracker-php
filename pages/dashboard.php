<?php
$user = currentUser();
$userId = currentUserId();

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

renderPage('Dashboard', function() use ($user, $totalWorkouts, $totalVolume, $totalSets, $workoutsThisWeek, $activeWorkout, $recentWorkouts, $amDone, $pmDone) {
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
                        <div class="recent-workout-date"><?= formatDate((int)$workout['started_at']) ?> - <?= e($workoutName) ?></div>
                    </div>
                    <span class="recent-workout-chevron">›</span>
                </a>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
    
    <form method="POST" action="/action/auth/logout" style="margin-top:40px;"><?= csrfField() ?><button type="submit" class="btn btn-small" style="width:auto;">Logout</button></form>
    <?php
});
