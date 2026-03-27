<?php
/**
 * Dashboard Page - Redesigned
 */

$user = currentUser();
$userId = currentUserId();

// Get stats
$totalWorkouts = dbFetchOne(
    "SELECT COUNT(*) as count FROM workouts WHERE user_id = ? AND ended_at IS NOT NULL",
    [$userId]
)['count'] ?? 0;

$totalVolume = dbFetchOne(
    "SELECT SUM(ws.weight * ws.reps) as total 
     FROM workout_sets ws 
     JOIN workouts w ON ws.workout_id = w.id 
     WHERE w.user_id = ? AND w.ended_at IS NOT NULL",
    [$userId]
)['total'] ?? 0;

$totalSets = dbFetchOne(
    "SELECT COUNT(*) as count FROM workout_sets ws 
     JOIN workouts w ON ws.workout_id = w.id 
     WHERE w.user_id = ? AND w.ended_at IS NOT NULL AND ws.completed_at IS NOT NULL",
    [$userId]
)['count'] ?? 0;

// Get this week's workouts
$weekStart = strtotime('monday this week') * 1000;
$workoutsThisWeek = dbFetchOne(
    "SELECT COUNT(*) as count FROM workouts WHERE user_id = ? AND ended_at IS NOT NULL AND started_at >= ?",
    [$userId, $weekStart]
)['count'] ?? 0;

// Get recent workouts with routine names
$recentWorkouts = dbFetchAll(
    "SELECT w.id, w.started_at, w.ended_at, w.routine_id, r.name as routine_name 
     FROM workouts w 
     LEFT JOIN routines r ON w.routine_id = r.id 
     WHERE w.user_id = ? AND w.ended_at IS NOT NULL 
     ORDER BY w.started_at DESC 
     LIMIT 5",
    [$userId]
);

// Check for active workout
$activeWorkout = dbFetchOne(
    "SELECT w.id, w.started_at, r.name as routine_name 
     FROM workouts w 
     LEFT JOIN routines r ON w.routine_id = r.id 
     WHERE w.user_id = ? AND w.ended_at IS NULL",
    [$userId]
);

renderPage('Dashboard', function() use ($user, $totalWorkouts, $totalVolume, $totalSets, $workoutsThisWeek, $recentWorkouts, $activeWorkout) {
    ?>
    <!-- Welcome Banner -->
    <div class="welcome-banner">
        Welcome back!
    </div>
    
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
    
    <!-- Stats Grid -->
    <div class="stats-grid">
        <div class="stat-box">
            <div class="stat-value"><?= $totalWorkouts ?></div>
            <div class="stat-label">Workouts</div>
        </div>
        <div class="stat-box">
            <div class="stat-value"><?= number_format($totalVolume / 1000, 1) ?>k</div>
            <div class="stat-label">Lbs Volume</div>
        </div>
        <div class="stat-box">
            <div class="stat-value"><?= $totalSets ?></div>
            <div class="stat-label">Total Sets</div>
        </div>
        <div class="stat-box">
            <div class="stat-value"><?= $workoutsThisWeek ?></div>
            <div class="stat-label">This Week</div>
        </div>
    </div>
    
    <!-- Start Routine Button -->
    <a href="/routines" class="btn btn-primary btn-chevron" style="margin-bottom: 24px;">
        START ROUTINE
    </a>
    
    <?php if (!empty($recentWorkouts)): ?>
        <div class="recent-workouts-title">RECENT WORKOUTS</div>
        <div class="card" style="padding: 0;">
            <?php foreach ($recentWorkouts as $workout): 
                $setCount = dbFetchOne(
                    "SELECT COUNT(*) as count FROM workout_sets WHERE workout_id = ? AND completed_at IS NOT NULL",
                    [$workout['id']]
                )['count'] ?? 0;
                $workoutName = $workout['routine_name'] ?: 'Freestyle Workout';
            ?>
                <a href="/workouts/view?id=<?= $workout['id'] ?>" class="recent-workout-item" style="text-decoration: none; color: inherit; padding: 14px 16px;">
                    <div class="recent-workout-info">
                        <div class="recent-workout-date"><?= formatDate((int)$workout['started_at']) ?> - <?= htmlspecialchars($workoutName) ?></div>
                        <div class="recent-workout-meta"><?= $setCount ?> sets completed</div>
                    </div>
                    <span class="recent-workout-chevron">›</span>
                </a>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
    
    <form method="POST" action="/action/auth/logout" style="margin-top: 40px;">
        <?= csrfField() ?>
        <button type="submit" class="btn btn-small" style="width: auto;">Logout</button>
    </form>
    <?php
});
