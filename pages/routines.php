<?php
requireAuth();
$db = getDB();
require_once __DIR__ . '/../includes/progression.php';

// Get user's routines with exercises
$stmt = $db->prepare("
    SELECT r.*, COUNT(re.id) as exercise_count 
    FROM routines r 
    LEFT JOIN routine_exercises re ON r.id = re.routine_id 
    WHERE r.user_id = ? 
    GROUP BY r.id 
    ORDER BY r.created_at DESC
");
$stmt->execute([$_SESSION['user_id']]);
$routines = $stmt->fetchAll();

// Get last weights once for all exercises
$lastWeights = getLastWeightsDetailed($db, $_SESSION['user_id']);

// Get exercises for each routine with calculated next weights
foreach ($routines as &$routine) {
    $stmt = $db->prepare("
        SELECT re.*, e.name as exercise_name 
        FROM routine_exercises re 
        JOIN exercises e ON re.exercise_id = e.id 
        WHERE re.routine_id = ? 
        ORDER BY re.id
    ");
    $stmt->execute([$routine['id']]);
    $exercises = $stmt->fetchAll();
    
    // Calculate next weight for each exercise
    foreach ($exercises as &$ex) {
        $exName = $ex['exercise_name'];
        $ex['next_weight'] = null;
        $ex['last_weight'] = null;
        $ex['progression_note'] = null;
        
        if (isset($lastWeights[$exName])) {
            $lastWeight = $lastWeights[$exName]['weight'];
            $ex['last_weight'] = $lastWeight;
            $ex['next_weight'] = getNextWeight($exName, $lastWeight);
            $ex['progression_note'] = getProgressionNote($exName, $lastWeight);
        } else {
            // Never done this exercise - use routine target or starter weight
            $ex['next_weight'] = $ex['weight'] ?? null;
        }
    }
    
    $routine['exercises'] = $exercises;
}
unset($routine, $ex); // Break references
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
        .routine-header { display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 12px; }
        .routine-name { font-size: 14px; font-weight: 700; color: #1a1a1a; text-transform: uppercase; }
        .routine-desc { font-size: 12px; color: #666; margin-bottom: 12px; }
        .exercise-list { font-size: 12px; color: #333; margin-bottom: 16px; }
        .exercise-item { padding: 8px 0; border-bottom: 1px solid #eee; display: flex; justify-content: space-between; align-items: center; }
        .exercise-item:last-child { border-bottom: none; }
        .exercise-info { flex: 1; }
        .exercise-name { font-weight: 700; color: #1a1a1a; }
        .exercise-target { color: #666; font-size: 11px; }
        .next-weight { 
            background: var(--accent); 
            color: #fff; 
            padding: 4px 10px; 
            border-radius: 4px; 
            font-weight: 700; 
            font-family: 'Space Mono', monospace;
            font-size: 12px;
            text-align: center;
            min-width: 70px;
        }
        .next-weight small {
            display: block;
            font-size: 9px;
            font-weight: 400;
            opacity: 0.9;
        }
        .next-weight.new-exercise {
            background: #666;
        }
        .btn-start { background: var(--accent); color: #fff; border: none; padding: 8px 16px; font-family: inherit; font-size: 12px; font-weight: 700; text-transform: uppercase; cursor: pointer; border-radius: 4px; width: 100%; }
        .btn-start:hover { background: #e55a2b; }
        .section-header { font-size: 11px; color: var(--text-dim); text-transform: uppercase; margin-bottom: 8px; letter-spacing: 1px; }
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
                        <div class="routine-header">
                            <div class="routine-name"><?php echo h($routine['name']); ?></div>
                        </div>
                        <?php if ($routine['description']): ?>
                            <div class="routine-desc"><?php echo h($routine['description']); ?></div>
                        <?php endif; ?>
                        
                        <div class="section-header">Next Weights (Based on Last Workout)</div>
                        <div class="exercise-list">
                            <?php foreach ($routine['exercises'] as $ex): ?>
                                <div class="exercise-item">
                                    <div class="exercise-info">
                                        <div class="exercise-name"><?php echo h($ex['exercise_name']); ?></div>
                                        <div class="exercise-target">
                                            <?php echo $ex['sets'] ?? 3; ?> sets x <?php echo $ex['reps'] ?? 10; ?> reps
                                        </div>
                                    </div>
                                    <?php if ($ex['next_weight']): ?>
                                        <div class="next-weight <?php echo $ex['last_weight'] ? '' : 'new-exercise'; ?>">
                                            <?php echo $ex['next_weight']; ?> lbs
                                            <?php if ($ex['progression_note']): ?>
                                                <small><?php echo $ex['progression_note']; ?></small>
                                            <?php elseif (!$ex['last_weight']): ?>
                                                <small>START</small>
                                            <?php endif; ?>
                                        </div>
                                    <?php else: ?>
                                        <div class="next-weight new-exercise">?</div>
                                    <?php endif; ?>
                                </div>
                            <?php endforeach; ?>
                        </div>
                        <form method="POST" action="/api/routine/start">
                            <?php echo Security::csrfField(); ?>
                            <input type="hidden" name="routine_id" value="<?php echo $routine['id']; ?>">
                            <button type="submit" class="btn-start">START ROUTINE</button>
                        </form>
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
