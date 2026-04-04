<?php
/**
 * Apply Progression Suggestions Action
 * 
 * Takes all suggested weight increases for a routine and applies them.
 * This is the "one click" update after reviewing progression suggestions.
 * 
 * @package WorkoutTracker\Actions
 * 
 * POST Parameters:
 * - routine_id: ID of the routine to apply suggestions to
 * - suggestions: JSON array of ['routine_exercise_id' => 'suggested_weight', ...]
 */

requireCsrf();

/** @var int Current authenticated user's ID */
$userId = currentUserId();

/** @var int The routine ID to update */
$routineId = intParam($_POST['routine_id'] ?? 0);

if ($routineId <= 0) {
    redirect('/routines', 'Invalid routine', 'error');
}

// Verify ownership
$routine = dbFetchOne(
    "SELECT * FROM routines WHERE id = ? AND user_id = ?",
    [$routineId, $userId]
);

if (!$routine) {
    redirect('/routines', 'Routine not found', 'error');
}

// Parse suggestions from JSON
$rawSuggestions = $_POST['suggestions'] ?? '[]';
$suggestions = json_decode($rawSuggestions, true);

if (!is_array($suggestions) || empty($suggestions)) {
    redirect("/routines/edit?id={$routineId}", 'No suggestions to apply', 'error');
}

// Track what we're updating
$appliedCount = 0;
$appliedExercises = [];

dbBegin();

try {
    foreach ($suggestions as $reId => $newWeight) {
        $reId = intval($reId);
        $newWeight = floatval($newWeight);
        
        if ($reId <= 0 || $newWeight <= 0) {
            continue;
        }
        
        // Verify this exercise belongs to this routine and user owns it
        $exercise = dbFetchOne(
            "SELECT re.*, e.name as exercise_name
             FROM routine_exercises re
             JOIN routines r ON re.routine_id = r.id
             JOIN exercises e ON re.exercise_id = e.id
             WHERE re.id = ? AND re.routine_id = ? AND r.user_id = ?",
            [$reId, $routineId, $userId]
        );
        
        if (!$exercise) {
            continue;
        }
        
        // Update the target weight
        dbUpdate(
            'routine_exercises',
            [
                'target_weight' => $newWeight,
                'updated_at' => now()
            ],
            'id = ?',
            [$reId]
        );
        
        $appliedCount++;
        $appliedExercises[] = $exercise['exercise_name'];
    }
    
    dbCommit();
    
    if ($appliedCount > 0) {
        $exerciseList = implode(', ', $appliedExercises);
        redirect(
            "/routines/edit?id={$routineId}", 
            "Updated {$appliedCount} exercises: {$exerciseList}"
        );
    } else {
        redirect("/routines/edit?id={$routineId}", 'No suggestions applied', 'error');
    }
    
} catch (Exception $e) {
    dbRollback();
    error_log("Failed to apply progression suggestions: " . $e->getMessage());
    redirect("/routines/edit?id={$routineId}", 'Error applying suggestions', 'error');
}
