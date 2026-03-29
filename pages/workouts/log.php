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

// Calculate total workout volume
$totalVolume = 0;
foreach ($currentExercises as $exName => $data) {
    $totalVolume += $data['totalVolume'];
}

$workoutName = $activeWorkout['notes'] ?? '';

renderPage('Log Workout', function() use ($activeWorkout, $workoutId, $exercisesByCategory, $lastWeights, $currentExercises, $totalVolume, $workoutName) {
    ?>
    <h1><?= $activeWorkout ? 'Workout In Progress' : 'Start Workout' ?></h1>
    
    <?php if (!$activeWorkout): ?>
        <!-- Start New Workout Form with Name -->
        <form method="POST" action="/action/workouts/start" style="margin-bottom: 24px;">
            <?= csrfField() ?>
            <div class="form-group" style="margin-bottom: 16px;">
                <label class="form-label">WORKOUT NAME (Optional)</label>
                <input type="text" name="workout_name" class="form-input" placeholder="e.g., Upper Push, Leg Day, etc." style="width: 100%;">
            </div>
            <button type="submit" class="btn btn-primary" style="width: 100%;">
                START NEW WORKOUT
            </button>
        </form>
        
        <p style="color: var(--text-dim); text-align: center; margin: 32px 0;">
            Or <a href="/routines" style="color: var(--accent);">start from a routine</a>
        </p>
        
    <?php else: ?>
        
        <!-- Workout Name Header -->
        <div style="background: var(--surface); border: 1px solid var(--border); border-radius: 12px; padding: 16px; margin-bottom: 20px;">
            <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 12px;">
                <div>
                    <div style="font-size: 11px; color: var(--text-dim); text-transform: uppercase; letter-spacing: 1px; margin-bottom: 4px;">Workout Name</div>
                    <div id="workout-name-display" style="font-size: 18px; font-weight: 700;">
                        <?= $workoutName ? htmlspecialchars($workoutName) : '<span style="color: var(--text-dim);">Unnamed Workout</span>' ?>
                    </div>
                </div>
                <button type="button" class="btn btn-small" style="width: auto;" onclick="toggleNameEdit()">✎ Edit</button>
            </div>
            
            <!-- Hidden Edit Form -->
            <form id="workout-name-form" method="POST" action="/action/workouts/update-name" style="display: none;">
                <?= csrfField() ?>
                <div style="display: flex; gap: 8px;">
                    <input type="text" name="workout_name" value="<?= htmlspecialchars($workoutName) ?>" class="form-input" placeholder="Workout name..." style="flex: 1;">
                    <button type="submit" class="btn btn-small btn-success" style="width: auto;">Save</button>
                    <button type="button" class="btn btn-small" style="width: auto;" onclick="toggleNameEdit()">Cancel</button>
                </div>
            </form>
            
            <!-- Workout Stats -->
            <div style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 12px; margin-top: 12px; padding-top: 12px; border-top: 1px solid var(--border);">
                <div style="text-align: center;">
                    <div style="font-size: 18px; font-weight: 700; color: var(--accent);"><?= count($currentExercises) ?></div>
                    <div style="font-size: 10px; color: var(--text-dim);">Exercises</div>
                </div>
                <div style="text-align: center;">
                    <div style="font-size: 18px; font-weight: 700; color: var(--accent);">
                        <?php 
                            $totalSets = 0;
                            foreach ($currentExercises as $ex) {
                                $totalSets += count($ex['sets']);
                            }
                            echo $totalSets;
                        ?>
                    </div>
                    <div style="font-size: 10px; color: var(--text-dim);">Sets</div>
                </div>
                <div style="text-align: center;">
                    <div style="font-size: 18px; font-weight: 700; color: var(--accent);"><?= number_format($totalVolume / 1000, 1) ?>k</div>
                    <div style="font-size: 10px; color: var(--text-dim);">Volume</div>
                </div>
            </div>
        </div>
        
        <?php if (!empty($currentExercises)): ?>
            <?php foreach ($currentExercises as $exName => $data): ?>
                <div class="exercise-card">
                    <div class="exercise-card-title" style="display: flex; justify-content: space-between; align-items: center;">
                        <?= e($exName) ?>
                        <form method="POST" action="/action/workouts/remove-exercise" style="display: inline;" onsubmit="return confirm('Remove <?= e($exName) ?> from this workout?');">
                            <?= csrfField() ?>
                            <input type="hidden" name="exercise_id" value="<?= $data['exercise_id'] ?>">
                            <button type="submit" class="btn btn-small btn-danger" style="padding: 6px 10px; font-size: 12px; width: auto;">✕ REMOVE</button>
                        </form>
                    </div>
                    
                    <?php foreach ($data['sets'] as $i => $set): ?>
                        <div class="set-row" id="set-row-<?= $set['id'] ?>">
                            <span class="set-label">Set <?= $i + 1 ?></span>
                            
                            <?php if ($set['completed_at']): ?>
                                <!-- View Mode -->
                                <div class="set-view" id="set-view-<?= $set['id'] ?>">
                                    <input type="text" class="set-input" value="<?= $set['reps'] ?>" readonly>
                                    <span class="set-unit">reps</span>
                                    <input type="text" class="set-input" value="<?= formatWeight($set['weight']) ?>" readonly>
                                    <span class="set-unit">lbs</span>
                                    <div class="set-check completed">
                                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3">
                                            <polyline points="20 6 9 17 4 12"></polyline>
                                        </svg>
                                    </div>
                                    <button type="button" class="btn btn-small" style="width: auto; padding: 8px 12px; margin-left: 8px; background: var(--accent); color: var(--bg);" onclick="showEditForm(<?= $set['id'] ?>)">✎ EDIT</button>
                                </div>
                                
                                <!-- Edit Mode (hidden by default) -->
                                <form method="POST" action="/action/workouts/edit-set" class="set-edit" id="set-edit-<?= $set['id'] ?>" style="display: none; flex: 1; align-items: center; gap: 8px;">
                                    <?= csrfField() ?>
                                    <input type="hidden" name="set_id" value="<?= $set['id'] ?>">
                                    <input type="number" name="reps" value="<?= $set['reps'] ?>" class="set-input" required min="1">
                                    <span class="set-unit">reps</span>
                                    <input type="number" name="weight" value="<?= $set['weight'] ?>" class="set-input" required min="0" step="0.5">
                                    <span class="set-unit">lbs</span>
                                    <button type="submit" class="btn btn-small btn-success" style="width: auto; padding: 8px 12px;">SAVE</button>
                                    <button type="button" class="btn btn-small" style="width: auto; padding: 8px 12px;" onclick="hideEditForm(<?= $set['id'] ?>)">CANCEL</button>
                                </form>
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
            function showEditForm(setId) {
                document.getElementById('set-view-' + setId).style.display = 'none';
                document.getElementById('set-edit-' + setId).style.display = 'flex';
            }
            
            function hideEditForm(setId) {
                document.getElementById('set-edit-' + setId).style.display = 'none';
                document.getElementById('set-view-' + setId).style.display = 'flex';
            }
            
            function toggleNameEdit() {
                const display = document.getElementById('workout-name-display');
                const form = document.getElementById('workout-name-form');
                
                if (form.style.display === 'none') {
                    form.style.display = 'block';
                    display.style.display = 'none';
                } else {
                    form.style.display = 'none';
                    display.style.display = 'block';
                }
            }
            
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
