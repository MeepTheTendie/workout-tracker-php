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

// Progression rules
$progressionRules = [
    'Back Extension' => ['increment' => 15, 'unit' => 'lbs'],
    'Low Back - Roc It' => ['increment' => 15, 'unit' => 'lbs', 'note' => 'Till 45, then 20'],
    'Diverging Seated Row' => ['increment' => 10, 'unit' => 'lbs'],
    'Leg Press' => ['increment' => 15, 'unit' => 'lbs'],
    'Converging Chest Press' => ['increment' => 15, 'unit' => 'lbs'],
    'Tricep Extensions' => ['increment' => 10, 'unit' => 'lbs'],
    'Bicep Curl' => ['increment' => 15, 'unit' => 'lbs'],
    'Shoulder Press - Machine' => ['increment' => 20, 'unit' => 'lbs'],
];

// Get last used weight for each exercise
$lastWeights = [];
$stmt = $db->prepare("SELECT e.name, ws.weight FROM workout_sets ws JOIN exercises e ON ws.exercise_id = e.id JOIN workouts w ON ws.workout_id = w.id WHERE w.user_id = ? AND w.ended_at IS NOT NULL ORDER BY ws.id DESC");
$stmt->execute([$_SESSION['user_id']]);
$allSets = $stmt->fetchAll();
foreach ($allSets as $set) {
    if (!isset($lastWeights[$set['name']])) {
        $lastWeights[$set['name']] = $set['weight'];
    }
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

// Group current sets by exercise
$currentExercises = [];
foreach ($workoutSets as $set) {
    $exName = $set['exercise_name'];
    if (!isset($currentExercises[$exName])) {
        $currentExercises[$exName] = ['sets' => [], 'totalVolume' => 0];
    }
    $currentExercises[$exName]['sets'][] = $set;
    $currentExercises[$exName]['totalVolume'] += ($set['weight'] * $set['reps']);
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
        .exercise-select select { width: 100%; padding: 12px; background: var(--bg); border: 1px solid var(--border); border-radius: 6px; color: var(--text); font-family: 'Space Mono', monospace; font-size: 14px; }
        .progression-hint { background: rgba(255,107,53,0.1); border: 1px solid var(--accent); border-radius: 6px; padding: 12px; margin-bottom: 16px; font-size: 12px; }
        .progression-hint strong { color: var(--accent); }
        .set-row { display: grid; grid-template-columns: 1fr 1fr; gap: 12px; margin-bottom: 12px; }
        .set-input { width: 100%; padding: 12px; background: var(--bg); border: 1px solid var(--border); border-radius: 6px; color: var(--text); font-family: 'Space Mono', monospace; font-size: 14px; }
        .btn-add { background: var(--surface); border: 2px dashed var(--border); color: var(--text); }
        .btn-add:hover { border-color: var(--accent); color: var(--accent); }
        .btn-finish { background: var(--success); }
        .current-sets { background: var(--surface); border: 1px solid var(--border); border-radius: 8px; padding: 16px; margin-bottom: 16px; }
        .current-sets h3 { font-size: 12px; color: var(--text-dim); text-transform: uppercase; margin-bottom: 12px; }
        .exercise-block { background: #fff; border-radius: 4px; padding: 12px; margin-bottom: 12px; }
        .exercise-block h4 { font-size: 13px; color: #1a1a1a; text-transform: uppercase; margin-bottom: 8px; }
        .set-line { display: flex; justify-content: space-between; font-size: 12px; color: #666; padding: 4px 0; border-bottom: 1px solid #eee; }
        .set-line:last-child { border-bottom: none; }
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
                <p style="color: var(--text-dim); margin-bottom: 16px;">Workout in progress</p>
                
                <?php if (!empty($currentExercises)): ?>
                    <div class="current-sets">
                        <h3>Current Exercises</h3>
                        <?php foreach ($currentExercises as $exName => $data): ?>
                            <div class="exercise-block">
                                <h4><?php echo h($exName); ?></h4>
                                <?php foreach ($data['sets'] as $i => $set): ?>
                                    <div class="set-line">
                                        <span>Set <?php echo $i + 1; ?>: <?php echo $set['reps']; ?> reps @ <?php echo $set['weight']; ?> lbs</span>
                                    </div>
                                <?php endforeach; ?>
                                <div class="set-line" style="margin-top: 8px; padding-top: 8px; border-top: 2px solid #eee; font-weight: 700;">
                                    <span>Volume: <?php echo number_format($data['totalVolume']); ?> lbs</span>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
                
                <form method="POST" action="/api/workout/set" style="margin-bottom: 16px;">
                    <div class="exercise-select">
                        <label>Select Exercise</label>
                        <select name="exercise_id" id="exerciseSelect" required onchange="showProgression()">
                            <option value="">Choose exercise...</option>
                            <?php foreach ($byCategory as $category => $exs): ?>
                                <optgroup label="<?php echo h($category); ?>">
                                    <?php foreach ($exs as $ex): ?>
                                        <option value="<?php echo $ex['id']; ?>" data-name="<?php echo h($ex['name']); ?>"><?php echo h($ex['name']); ?></option>
                                    <?php endforeach; ?>
                                </optgroup>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div id="progressionHint" class="progression-hint" style="display: none;">
                        <!-- Filled by JS -->
                    </div>
                    
                    <div class="set-row">
                        <input type="number" name="reps" placeholder="Reps" class="set-input" required min="1">
                        <input type="number" name="weight" id="weightInput" placeholder="Weight (lbs)" class="set-input" required min="0" step="0.5">
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
    
    <script>
        const progressionRules = <?php echo json_encode($progressionRules); ?>;
        const lastWeights = <?php echo json_encode($lastWeights); ?>;
        
        function showProgression() {
            const select = document.getElementById('exerciseSelect');
            const hint = document.getElementById('progressionHint');
            const weightInput = document.getElementById('weightInput');
            const option = select.options[select.selectedIndex];
            const name = option.getAttribute('data-name');
            
            if (!name) {
                hint.style.display = 'none';
                return;
            }
            
            let html = '';
            
            // Show last weight
            if (lastWeights[name]) {
                html += `<div>Last time: <strong>${lastWeights[name]} lbs</strong></div>`;
            }
            
            // Show progression rule
            if (progressionRules[name]) {
                const rule = progressionRules[name];
                const nextWeight = lastWeights[name] ? parseFloat(lastWeights[name]) + rule.increment : rule.increment;
                html += `<div style="margin-top: 8px;">Next: Try <strong>${nextWeight} lbs</strong> (+${rule.increment}${rule.unit})`;
                if (rule.note) {
                    html += `<br><small style="color: var(--text-dim);">Note: ${rule.note}</small>`;
                }
                html += `</div>`;
                
                // Auto-fill suggestion
                if (!weightInput.value && lastWeights[name]) {
                    weightInput.value = nextWeight;
                }
            }
            
            if (html) {
                hint.innerHTML = html;
                hint.style.display = 'block';
            } else {
                hint.style.display = 'none';
            }
        }
    </script>
</body>
</html>
