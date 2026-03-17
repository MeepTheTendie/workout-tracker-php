<?php
requireAuth();
$db = getDB();

// Get all exercises for dropdown
$stmt = $db->query("SELECT * FROM exercises ORDER BY category, name");
$exercises = $stmt->fetchAll();

// Group by category
$byCategory = [];
foreach ($exercises as $ex) {
    $byCategory[$ex['category']][] = $ex;
}

// Check for active workout
$stmt = $db->prepare("SELECT * FROM workouts WHERE user_id = ? AND ended_at IS NULL ORDER BY started_at DESC LIMIT 1");
$stmt->execute([$_SESSION['user_id']]);
$activeWorkout = $stmt->fetch();

$workoutId = $activeWorkout ? $activeWorkout['id'] : null;

// Get sets for active workout
$workoutSets = [];
if ($workoutId) {
    $stmt = $db->prepare("SELECT ws.*, e.name as exercise_name FROM workout_sets ws JOIN exercises e ON ws.exercise_id = e.id WHERE ws.workout_id = ? ORDER BY ws.id");
    $stmt->execute([$workoutId]);
    $workoutSets = $stmt->fetchAll();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Log Workout - Workout Tracker</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Space+Mono:wght@400;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="/css/style.css">
    <style>
        .exercise-select { margin-bottom: 16px; }
        .exercise-select label { display: block; font-size: 11px; color: var(--text-dim); text-transform: uppercase; margin-bottom: 8px; }
        .exercise-select select { width: 100%; padding: 12px; background: var(--bg); border: 1px solid var(--border); border-radius: 6px; color: var(--text); font-family: 'Space Mono', monospace; }
        .set-row { display: grid; grid-template-columns: 1fr 1fr auto; gap: 12px; margin-bottom: 12px; }
        .set-input { padding: 12px; background: var(--bg); border: 1px solid var(--border); border-radius: 6px; color: var(--text); font-family: 'Space Mono', monospace; }
        .btn-add { background: var(--surface); border: 1px dashed var(--border); color: var(--text-dim); }
        .btn-add:hover { border-color: var(--accent); color: var(--accent); }
        .btn-finish { background: var(--success); }
        .current-sets { background: var(--surface); border: 1px solid var(--border); border-radius: 8px; padding: 16px; margin-bottom: 16px; }
        .current-sets h3 { font-size: 12px; color: var(--text-dim); text-transform: uppercase; margin-bottom: 12px; }
        .set-item { display: flex; justify-content: space-between; padding: 8px 0; border-bottom: 1px solid var(--border); font-size: 13px; }
        .set-item:last-child { border-bottom: none; }
    </style>
</head>
<body>
    <div class="app">
        <div class="content">
            <h1 style="font-size: 20px; text-transform: uppercase; letter-spacing: 1px; margin-bottom: 8px;">LOG WORKOUT</h1>
            
            <?php if (!$activeWorkout): ?>
                <form method="POST" action="/api/workout/start" style="margin-bottom: 24px;">
                    <button type="submit" class="btn">START NEW WORKOUT</button>
                </form>
            <?php else: ?>
                <p style="color: var(--text-dim); margin-bottom: 16px;">Workout started</p>
                
                <?php if (!empty($workoutSets)): ?>
                    <div class="current-sets">
                        <h3>Current Sets</h3>
                        <?php 
                        $currentEx = '';
                        foreach ($workoutSets as $set):
                            if ($set['exercise_name'] !== $currentEx):
                                $currentEx = $set['exercise_name'];
                                echo '<div style="font-weight: 700; margin-top: 8px; color: var(--accent);">' . h($currentEx) . '</div>';
                            endif;
                        ?>
                            <div class="set-item">
                                <span>Set <?php echo $set['set_number']; ?></span>
                                <span><?php echo $set['reps']; ?> reps @ <?php echo $set['weight']; ?> lbs</span>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
                
                <form method="POST" action="/api/workout/set" style="margin-bottom: 16px;">
                    <div class="exercise-select">
                        <label>Select Exercise</label>
                        <select name="exercise_id" required>
                            <option value="">Choose exercise...</option>
                            <?php foreach ($byCategory as $category => $exs): ?>
                                <optgroup label="<?php echo h($category); ?>">
                                    <?php foreach ($exs as $ex): ?>
                                        <option value="<?php echo $ex['id']; ?>"><?php echo h($ex['name']); ?></option>
                                    <?php endforeach; ?>
                                </optgroup>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div class="set-row">
                        <input type="number" name="reps" placeholder="Reps" class="set-input" required min="1">
                        <input type="number" name="weight" placeholder="Weight (lbs)" class="set-input" required min="0" step="0.5">
                    </div>
                    
                    <button type="submit" class="btn btn-add" style="width: 100%;">+ ADD SET</button>
                </form>
                
                <form method="POST" action="/api/workout/finish">
                    <button type="submit" class="btn btn-finish" style="width: 100%;">FINISH WORKOUT</button>
                </form>
            <?php endif; ?>
        </div>
        
        <nav>
            <a href="/dashboard" class="nav-btn">Home</a>
            <a href="/workouts/create" class="nav-btn active">Log</a>
            <a href="/workouts" class="nav-btn">History</a>
            <a href="/stats" class="nav-btn">Stats</a>
            <a href="/prs" class="nav-btn">PRs</a>
        </nav>
    </div>
</body>
</html>
