<?php
/**
 * Edit Routine Page
 */

$userId = currentUserId();
$routineId = intParam($_GET['id'] ?? 0);

if ($routineId <= 0) {
    redirect('/routines', 'Invalid routine', 'error');
}

$routine = dbFetchOne(
    "SELECT * FROM routines WHERE id = ? AND user_id = ?",
    [$routineId, $userId]
);

if (!$routine) {
    redirect('/routines', 'Routine not found', 'error');
}

// Get current exercises in routine
$routineExercises = dbFetchAll(
    "SELECT re.*, e.name as exercise_name, e.category 
     FROM routine_exercises re 
     JOIN exercises e ON re.exercise_id = e.id 
     WHERE re.routine_id = ? 
     ORDER BY re.order_index",
    [$routineId]
);

// Get all exercises for adding new ones
$exercisesByCategory = getExercisesByCategory();

renderPage('Edit Routine', function() use ($routine, $routineExercises, $exercisesByCategory) {
    ?>
    <h1>Edit Routine</h1>
    
    <form method="POST" action="/action/routines/update" style="margin-bottom: 32px;">
        <?= csrfField() ?>
        <input type="hidden" name="routine_id" value="<?= $routine['id'] ?>">
        
        <div class="form-group">
            <label class="form-label">Routine Name</label>
            <input type="text" name="name" class="form-input" value="<?= e($routine['name']) ?>" required>
        </div>
        
        <div class="form-group">
            <label class="form-label">Description</label>
            <input type="text" name="description" class="form-input" value="<?= e($routine['description'] ?? '') ?>">
        </div>
        
        <button type="submit" class="btn btn-primary">UPDATE ROUTINE</button>
    </form>
    
    <h2>Exercises</h2>
    
    <?php if (!empty($routineExercises)): ?>
        <div style="margin-bottom: 24px;">
            <?php foreach ($routineExercises as $ex): ?>
                <div class="card">
                    <form method="POST" action="/action/routines/update-exercise">
                        <?= csrfField() ?>
                        <input type="hidden" name="routine_id" value="<?= $routine['id'] ?>">
                        <input type="hidden" name="exercise_id" value="<?= $ex['exercise_id'] ?>">
                        
                        <div class="exercise-header" style="align-items: flex-start;">
                            <div style="flex: 1;">
                                <div class="exercise-name"><?= e($ex['exercise_name']) ?></div>
                                <div class="form-row" style="margin-top: 12px; gap: 8px;">
                                    <div class="form-group" style="margin: 0; flex: 1;">
                                        <label style="font-size: 12px; color: var(--text-muted);">Sets</label>
                                        <input type="number" name="target_sets" class="form-input" 
                                               value="<?= $ex['target_sets'] ?>" min="1" style="padding: 6px 10px;">
                                    </div>
                                    <div class="form-group" style="margin: 0; flex: 1;">
                                        <label style="font-size: 12px; color: var(--text-muted);">Reps</label>
                                        <input type="number" name="target_reps" class="form-input" 
                                               value="<?= $ex['target_reps'] ?>" min="1" style="padding: 6px 10px;">
                                    </div>
                                    <div class="form-group" style="margin: 0; flex: 1;">
                                        <label style="font-size: 12px; color: var(--text-muted);">Weight (lbs)</label>
                                        <input type="number" name="target_weight" class="form-input" 
                                               value="<?= $ex['target_weight'] ?: '' ?>" min="0" step="0.5" 
                                               style="padding: 6px 10px;">
                                    </div>
                                </div>
                            </div>
                            <div style="display: flex; gap: 8px; margin-left: 12px;">
                                <button type="submit" class="btn btn-small btn-primary" style="width: auto;">✓</button>
                                <button type="submit" formaction="/action/routines/remove-exercise" 
                                        class="btn btn-small btn-danger" 
                                        onclick="return confirm('Remove this exercise?')" style="width: auto;">×</button>
                            </div>
                        </div>
                    </form>
                </div>
            <?php endforeach; ?>
        </div>
    <?php else: ?>
        <div class="empty" style="padding: 20px;">
            <p>No exercises added yet</p>
        </div>
    <?php endif; ?>
    
    <div class="card" style="background: var(--bg);">
        <h3 style="margin-bottom: 16px;">Add Exercise</h3>
        
        <form method="POST" action="/action/routines/add-exercise">
            <?= csrfField() ?>
            <input type="hidden" name="routine_id" value="<?= $routine['id'] ?>">
            
            <div class="form-group">
                <label class="form-label">Exercise</label>
                <select name="exercise_id" class="form-select" required>
                    <option value="">Select exercise...</option>
                    <?php foreach ($exercisesByCategory as $category => $exercises): ?>
                        <optgroup label="<?= e($category) ?>">
                            <?php foreach ($exercises as $ex): ?>
                                <option value="<?= $ex['id'] ?>"><?= e($ex['name']) ?></option>
                            <?php endforeach; ?>
                        </optgroup>
                    <?php endforeach; ?>
                </select>
            </div>
            
            <div class="form-row">
                <div class="form-group">
                    <label class="form-label">Sets</label>
                    <input type="number" name="target_sets" class="form-input" value="3" required min="1">
                </div>
                <div class="form-group">
                    <label class="form-label">Reps</label>
                    <input type="number" name="target_reps" class="form-input" value="10" required min="1">
                </div>
                <div class="form-group">
                    <label class="form-label">Weight (lbs)</label>
                    <input type="number" name="target_weight" class="form-input" placeholder="0" min="0" step="0.5">
                </div>
            </div>
            
            <button type="submit" class="btn btn-primary">ADD EXERCISE</button>
        </form>
    </div>
    
    <a href="/routines" class="btn" style="margin-top: 24px;">← Back to Routines</a>
    <?php
});
