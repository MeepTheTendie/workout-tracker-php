<?php
/**
 * Workout Log Page - Redesigned
 * Handles both freestyle and routine-based workouts
 */

$userId = currentUserId();

// Get active workout
$activeWorkout = dbFetchOne(
    "SELECT * FROM workouts WHERE user_id = ? AND ended_at IS NULL ORDER BY started_at DESC LIMIT 1",
    [$userId]
);

$workoutId = $activeWorkout ? $activeWorkout['id'] : null;

// Get exercises for dropdown
$exercisesByCategory = getExercisesByCategory();

// Get last weights for progression suggestions
$lastWeights = getLastWeights($userId);

// Get current sets if workout exists
$currentExercises = [];
if ($workoutId) {
    $sets = dbFetchAll(
        "SELECT ws.*, e.name as exercise_name, e.category 
         FROM workout_sets ws 
         JOIN exercises e ON ws.exercise_id = e.id 
         WHERE ws.workout_id = ? 
         ORDER BY ws.id",
        [$workoutId]
    );
    
    foreach ($sets as $set) {
        $exName = $set['exercise_name'];
        if (!isset($currentExercises[$exName])) {
            $currentExercises[$exName] = [
                'sets' => [],
                'totalVolume' => 0,
                'exercise_id' => $set['exercise_id'],
                'category' => $set['category']
            ];
        }
        $currentExercises[$exName]['sets'][] = $set;
        if ($set['completed_at']) {
            $currentExercises[$exName]['totalVolume'] += ($set['weight'] * $set['reps']);
        }
    }
}

renderPage('Log Workout', function() use ($activeWorkout, $workoutId, $exercisesByCategory, $lastWeights, $currentExercises) {
    ?>
    <h1><?= $activeWorkout ? 'Workout In Progress' : 'Start Workout' ?></h1>
    
    <?php if (!$activeWorkout): ?>
        <form method="POST" action="/action/workouts/start" style="margin-bottom: 24px;">
            <?= csrfField() ?>
            <button type="submit" class="btn btn-primary">
                START NEW WORKOUT
            </button>
        </form>
        
        <p style="color: var(--text-dim); text-align: center; margin: 32px 0;">
            Or <a href="/routines" style="color: var(--accent);">start from a routine</a>
        </p>
        
    <?php else: ?>
        
        <?php if (!empty($currentExercises)): ?>
            <?php foreach ($currentExercises as $exName => $data): ?>
                <div class="exercise-card">
                    <div class="exercise-card-title"><?= e($exName) ?></div>
                    
                    <?php foreach ($data['sets'] as $i => $set): ?>
                        <div class="set-row">
                            <span class="set-label">Set <?= $i + 1 ?></span>
                            
                            <?php if ($set['completed_at']): ?>
                                <input type="text" class="set-input" value="<?= $set['reps'] ?>" readonly>
                                <span class="set-unit">reps</span>
                                <input type="text" class="set-input" value="<?= formatWeight($set['weight']) ?>" readonly>
                                <span class="set-unit">lbs</span>
                                <div class="set-check completed">
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3">
                                        <polyline points="20 6 9 17 4 12"></polyline>
                                    </svg>
                                </div>
                            <?php else: ?>
                                <form method="POST" action="/action/workouts/complete-set" style="display: flex; align-items: center; gap: 8px; flex: 1;">
                                    <?= csrfField() ?>
                                    <input type="hidden" name="set_id" value="<?= $set['id'] ?>">
                                    <input type="number" name="reps" value="<?= $set['reps'] ?? '10' ?>" class="set-input" required min="1">
                                    <span class="set-unit">reps</span>
                                    <input type="number" name="weight" value="<?= $set['weight'] ?? '' ?>" class="set-input" required min="0" step="0.5">
                                    <span class="set-unit">lbs</span>
                                    <button type="submit" class="btn btn-small btn-primary" style="width: auto; padding: 10px 16px;">LOG</button>
                                </form>
                            <?php endif; ?>
                        </div>
                    <?php endforeach; ?>
                    
                    <!-- Add Set Button for this exercise -->
                    <form method="POST" action="/action/workouts/add-set" style="margin-top: 12px;">
                        <?= csrfField() ?>
                        <input type="hidden" name="exercise_id" value="<?= $data['exercise_id'] ?>">
                        <input type="hidden" name="reps" value="<?= $data['sets'][count($data['sets'])-1]['reps'] ?? 10 ?>">
                        <input type="hidden" name="weight" value="<?= $data['sets'][count($data['sets'])-1]['weight'] ?? 100 ?>">
                        <button type="submit" class="btn btn-add-set">+ ADD SET</button>
                    </form>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
        
        <!-- Add New Exercise Section -->
        <div class="add-exercise-section">
            <div class="add-exercise-title">ADD NEW EXERCISE TO WORKOUT</div>
            
            <form method="POST" action="/action/workouts/add-set">
                <?= csrfField() ?>
                
                <div class="form-group">
                    <label class="form-label">EXERCISE</label>
                    <select name="exercise_id" id="exerciseSelect" class="form-select" required onchange="showProgression()">
                        <option value="">Select exercise...</option>
                        <?php foreach ($exercisesByCategory as $category => $exercises): ?>
                            <optgroup label="<?= e($category) ?>">
                                <?php foreach ($exercises as $ex): ?>
                                    <option value="<?= $ex['id'] ?>" data-name="<?= e($ex['name']) ?>">
                                        <?= e($ex['name']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </optgroup>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div id="progressionHint" class="progression-hint" style="display: none;"></div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label">REPS</label>
                        <input type="number" name="reps" class="form-input" placeholder="10" required min="1">
                    </div>
                    <div class="form-group">
                        <label class="form-label">WEIGHT (LBS)</label>
                        <input type="number" name="weight" id="weightInput" class="form-input" placeholder="100" required min="0" step="0.5">
                    </div>
                </div>
                
                <button type="submit" class="btn btn-primary">ADD EXERCISE</button>
            </form>
        </div>
        
        <form method="POST" action="/action/workouts/finish" class="finish-workout-btn">
            <?= csrfField() ?>
            <button type="submit" class="btn btn-success">FINISH WORKOUT</button>
        </form>
        
        <script>
            const lastWeights = <?= json_encode($lastWeights) ?>;
            
            function showProgression() {
                const select = document.getElementById('exerciseSelect');
                const hint = document.getElementById('progressionHint');
                const weightInput = document.getElementById('weightInput');
                const option = select.options[select.selectedIndex];
                const name = option.getAttribute('data-name');
                
                if (!name || !lastWeights[name]) {
                    hint.style.display = 'none';
                    return;
                }
                
                const lastWeight = lastWeights[name];
                const suggestions = {
                    'Back Extension': { inc: 15, special: null },
                    'Low Back - Roc It': { inc: 15, special: 'roc_it' },
                    'Diverging Seated Row': { inc: 10, special: null },
                    'Leg Press': { inc: 15, special: null },
                    'Bicep Curl': { inc: 15, special: null },
                    'Shoulder Press - Machine': { inc: 20, special: null }
                };
                
                let html = '<div>Last time: <strong>' + lastWeight + ' lbs</strong></div>';
                
                if (suggestions[name]) {
                    let inc = suggestions[name].inc;
                    if (suggestions[name].special === 'roc_it' && lastWeight >= 45) {
                        inc = 20;
                    }
                    const next = lastWeight + inc;
                    html += '<div style="margin-top: 4px;">Next: Try <strong>' + next + ' lbs</strong> (+' + inc + ')</div>';
                    
                    if (!weightInput.value) {
                        weightInput.value = next;
                    }
                }
                
                hint.innerHTML = html;
                hint.style.display = 'block';
            }
        </script>
    <?php endif; ?>
    <?php
});
