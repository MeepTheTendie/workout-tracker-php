<?php
requireAuth();
$db = getDB();

// Get PRs for each exercise
$stmt = $db->prepare("SELECT e.name, MAX(ws.weight) as max_weight, ws.reps as max_reps FROM workout_sets ws JOIN exercises e ON ws.exercise_id = e.id JOIN workouts w ON ws.workout_id = w.id WHERE w.user_id = ? AND w.ended_at IS NOT NULL GROUP BY e.id ORDER BY max_weight DESC");
$stmt->execute([$_SESSION['user_id']]);
$maxWeightPRs = $stmt->fetchAll();

// Get volume PRs (best total volume per exercise in a single workout)
$stmt = $db->prepare("SELECT e.name, SUM(ws.weight * ws.reps) as volume FROM workout_sets ws JOIN exercises e ON ws.exercise_id = e.id JOIN workouts w ON ws.workout_id = w.id WHERE w.user_id = ? AND w.ended_at IS NOT NULL GROUP BY e.id, ws.workout_id ORDER BY volume DESC LIMIT 20");
$stmt->execute([$_SESSION['user_id']]);
$volumePRs = $stmt->fetchAll();

// Get best single set (highest weight * reps)
$stmt = $db->prepare("SELECT e.name, ws.weight, ws.reps, (ws.weight * ws.reps) as volume FROM workout_sets ws JOIN exercises e ON ws.exercise_id = e.id JOIN workouts w ON ws.workout_id = w.id WHERE w.user_id = ? AND w.ended_at IS NOT NULL ORDER BY volume DESC LIMIT 20");
$stmt->execute([$_SESSION['user_id']]);
$bestSets = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PRs - Workout Tracker</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Space+Mono:wght@400;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="/css/style.css">
    <style>
        .section-card { background: var(--surface); border: 1px solid var(--border); border-radius: 8px; padding: 16px; margin-bottom: 16px; }
        .section-title { font-size: 12px; color: var(--text-dim); text-transform: uppercase; letter-spacing: 1px; margin-bottom: 16px; }
        .pr-row { display: flex; justify-content: space-between; padding: 12px 0; border-bottom: 1px solid var(--border); }
        .pr-row:last-child { border-bottom: none; }
        .pr-exercise { color: var(--text); font-weight: 700; }
        .pr-value { color: var(--accent); font-size: 18px; font-weight: 700; }
        .pr-sub { color: var(--text-dim); font-size: 11px; }
    </style>
</head>
<body>
    <div class="app">
        <div class="content">
            <h1 style="font-size: 20px; text-transform: uppercase; letter-spacing: 1px; margin-bottom: 24px;">PERSONAL RECORDS</h1>
            
            <div class="section-card">
                <div class="section-title">Heaviest Weight</div>
                <?php if (empty($maxWeightPRs)): ?>
                    <p style="color: var(--text-dim); font-size: 13px;">No data yet</p>
                <?php else: ?>
                    <?php foreach ($maxWeightPRs as $pr): ?>
                        <div class="pr-row">
                            <div>
                                <div class="pr-exercise"><?php echo h($pr['name']); ?></div>
                            </div>
                            <div style="text-align: right;">
                                <div class="pr-value"><?php echo $pr['max_weight']; ?> lbs</div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
            
            <div class="section-card">
                <div class="section-title">Best Single Sets (Weight × Reps)</div>
                <?php if (empty($bestSets)): ?>
                    <p style="color: var(--text-dim); font-size: 13px;">No data yet</p>
                <?php else: ?>
                    <?php foreach ($bestSets as $pr): ?>
                        <div class="pr-row">
                            <div>
                                <div class="pr-exercise"><?php echo h($pr['name']); ?></div>
                                <div class="pr-sub"><?php echo $pr['weight']; ?> lbs × <?php echo $pr['reps']; ?> reps</div>
                            </div>
                            <div style="text-align: right;">
                                <div class="pr-value"><?php echo number_format($pr['volume']); ?></div>
                                <div class="pr-sub">volume</div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
        
        <nav>
            <a href="/dashboard" class="nav-btn">Home</a>
            <a href="/workouts/create" class="nav-btn">Log</a>
            <a href="/workouts" class="nav-btn">History</a>
            <a href="/stats" class="nav-btn">Stats</a>
            <a href="/prs" class="nav-btn active">PRs</a>
        </nav>
    </div>
</body>
</html>
