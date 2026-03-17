<?php
requireAuth();
$db = getDB();

// Get overall stats
$stmt = $db->prepare("SELECT COUNT(*) as total_workouts FROM workouts WHERE user_id = ? AND ended_at IS NOT NULL");
$stmt->execute([$_SESSION['user_id']]);
$totalWorkouts = $stmt->fetch()['total_workouts'];

$stmt = $db->prepare("SELECT SUM(weight * reps) as total_volume FROM workout_sets ws JOIN workouts w ON ws.workout_id = w.id WHERE w.user_id = ? AND w.ended_at IS NOT NULL");
$stmt->execute([$_SESSION['user_id']]);
$totalVolume = $stmt->fetch()['total_volume'] ?? 0;

$stmt = $db->prepare("SELECT COUNT(*) as total_sets FROM workout_sets ws JOIN workouts w ON ws.workout_id = w.id WHERE w.user_id = ? AND w.ended_at IS NOT NULL");
$stmt->execute([$_SESSION['user_id']]);
$totalSets = $stmt->fetch()['total_sets'];

// Get most frequent exercises
$stmt = $db->prepare("SELECT e.name, COUNT(*) as count FROM workout_sets ws JOIN exercises e ON ws.exercise_id = e.id JOIN workouts w ON ws.workout_id = w.id WHERE w.user_id = ? AND w.ended_at IS NOT NULL GROUP BY e.id ORDER BY count DESC LIMIT 10");
$stmt->execute([$_SESSION['user_id']]);
$topExercises = $stmt->fetchAll();

// Get recent workout frequency
$stmt = $db->prepare("SELECT DATE_FORMAT(FROM_UNIXTIME(started_at/1000), '%Y-%m') as month, COUNT(*) as count FROM workouts WHERE user_id = ? AND ended_at IS NOT NULL GROUP BY month ORDER BY month DESC LIMIT 6");
$stmt->execute([$_SESSION['user_id']]);
$monthlyStats = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Stats - Workout Tracker</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Space+Mono:wght@400;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="/css/style.css">
    <style>
        .stats-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 12px; margin-bottom: 24px; }
        .stat-card { background: #fff; border-radius: 4px; padding: 20px; text-align: center; }
        .stat-value { font-size: 32px; font-weight: 700; color: #1a1a1a; }
        .stat-label { font-size: 11px; color: #666; text-transform: uppercase; letter-spacing: 1px; margin-top: 4px; }
        .section-card { background: var(--surface); border: 1px solid var(--border); border-radius: 8px; padding: 16px; margin-bottom: 16px; }
        .section-title { font-size: 12px; color: var(--text-dim); text-transform: uppercase; letter-spacing: 1px; margin-bottom: 16px; }
        .exercise-row { display: flex; justify-content: space-between; padding: 8px 0; border-bottom: 1px solid var(--border); font-size: 13px; }
        .exercise-row:last-child { border-bottom: none; }
        .exercise-name { color: var(--text); }
        .exercise-count { color: var(--accent); font-weight: 700; }
    </style>
</head>
<body>
    <div class="app">
        <div class="content">
            <h1 style="font-size: 20px; text-transform: uppercase; letter-spacing: 1px; margin-bottom: 24px;">STATS</h1>
            
            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-value"><?php echo $totalWorkouts; ?></div>
                    <div class="stat-label">Workouts</div>
                </div>
                <div class="stat-card">
                    <div class="stat-value"><?php echo number_format($totalSets); ?></div>
                    <div class="stat-label">Total Sets</div>
                </div>
                <div class="stat-card" style="grid-column: 1 / -1;">
                    <div class="stat-value"><?php echo number_format($totalVolume / 1000, 1); ?>k</div>
                    <div class="stat-label">Total Volume (lbs)</div>
                </div>
            </div>
            
            <div class="section-card">
                <div class="section-title">Top Exercises</div>
                <?php if (empty($topExercises)): ?>
                    <p style="color: var(--text-dim); font-size: 13px;">No data yet</p>
                <?php else: ?>
                    <?php foreach ($topExercises as $ex): ?>
                        <div class="exercise-row">
                            <span class="exercise-name"><?php echo h($ex['name']); ?></span>
                            <span class="exercise-count"><?php echo $ex['count']; ?> sets</span>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
            
            <div class="section-card">
                <div class="section-title">Monthly Activity</div>
                <?php if (empty($monthlyStats)): ?>
                    <p style="color: var(--text-dim); font-size: 13px;">No data yet</p>
                <?php else: ?>
                    <?php foreach ($monthlyStats as $m): ?>
                        <div class="exercise-row">
                            <span class="exercise-name"><?php echo $m['month']; ?></span>
                            <span class="exercise-count"><?php echo $m['count']; ?> workouts</span>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
        
        <nav>
            <a href="/dashboard" class="nav-btn">Home</a>
            <a href="/workouts/create" class="nav-btn">Log</a>
            <a href="/workouts" class="nav-btn">History</a>
            <a href="/stats" class="nav-btn active">Stats</a>
            <a href="/prs" class="nav-btn">PRs</a>
        </nav>
    </div>
</body>
</html>
