<?php
requireAuth();
$db = getDB();

// Get user's routines with exercises
$stmt = $db->prepare("SELECT r.*, COUNT(re.id) as exercise_count FROM routines r LEFT JOIN routine_exercises re ON r.id = re.routine_id WHERE r.user_id = ? GROUP BY r.id ORDER BY r.created_at DESC");
$stmt->execute([$_SESSION['user_id']]);
$routines = $stmt->fetchAll();

// Get exercises for each routine
foreach ($routines as &$routine) {
    $stmt = $db->prepare("SELECT re.*, e.name as exercise_name FROM routine_exercises re JOIN exercises e ON re.exercise_id = e.id WHERE re.routine_id = ? ORDER BY re.id");
    $stmt->execute([$routine['id']]);
    $routine['exercises'] = $stmt->fetchAll();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Routines - Workout Tracker</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Space+Mono:wght@400;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="/css/style.css">
    <style>
        .routine-card { background: #fff; border-radius: 4px; padding: 16px; margin-bottom: 12px; }
        .routine-name { font-size: 14px; font-weight: 700; color: #1a1a1a; text-transform: uppercase; margin-bottom: 8px; }
        .routine-desc { font-size: 12px; color: #666; margin-bottom: 12px; }
        .exercise-list { font-size: 12px; color: #333; }
        .exercise-item { padding: 4px 0; border-bottom: 1px solid #eee; }
        .exercise-item:last-child { border-bottom: none; }
    </style>
</head>
<body>
    <div class="app">
        <div class="content">
            <h1 style="font-size: 20px; text-transform: uppercase; letter-spacing: 1px; margin-bottom: 24px;">ROUTINES</h1>
            
            <?php if (empty($routines)): ?>
                <div class="section-card">
                    <p style="color: var(--text-dim);">No routines created yet.</p>
                </div>
            <?php else: ?>
                <?php foreach ($routines as $routine): ?>
                    <div class="routine-card">
                        <div class="routine-name"><?php echo h($routine['name']); ?></div>
                        <?php if ($routine['description']): ?>
                            <div class="routine-desc"><?php echo h($routine['description']); ?></div>
                        <?php endif; ?>
                        <div class="exercise-list">
                            <?php foreach ($routine['exercises'] as $ex): ?>
                                <div class="exercise-item">
                                    <?php echo h($ex['exercise_name']); ?>
                                    <?php if ($ex['sets']): ?> - <?php echo $ex['sets']; ?> sets<?php endif; ?>
                                    <?php if ($ex['reps']): ?> × <?php echo $ex['reps']; ?> reps<?php endif; ?>
                                    <?php if ($ex['weight']): ?> @ <?php echo $ex['weight']; ?> lbs<?php endif; ?>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
        
        <nav>
            <a href="/dashboard" class="nav-btn">Home</a>
            <a href="/workouts/create" class="nav-btn">Log</a>
            <a href="/workouts" class="nav-btn">History</a>
            <a href="/stats" class="nav-btn">Stats</a>
            <a href="/prs" class="nav-btn">PRs</a>
        </nav>
    </div>
</body>
</html>
