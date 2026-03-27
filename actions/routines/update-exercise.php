<?php
/**
 * Update Routine Exercise Action
 * Updates target sets, reps, and weight for an exercise in a routine
 */

requireCsrf();

$userId = currentUserId();
$routineId = intParam($_POST['routine_id'] ?? 0);
$exerciseId = intParam($_POST['exercise_id'] ?? 0);
$targetSets = intParam($_POST['target_sets'] ?? 3);
$targetReps = intParam($_POST['target_reps'] ?? 10);
$targetWeight = floatParam($_POST['target_weight'] ?? 0);

if ($routineId <= 0 || $exerciseId <= 0) {
    redirect('/routines', 'Invalid data', 'error');
}

// Verify ownership
if (!ownsResource('routines', $routineId)) {
    redirect('/routines', 'Not authorized', 'error');
}

// Update the exercise in the routine
dbUpdate('routine_exercises', [
    'target_sets' => $targetSets,
    'target_reps' => $targetReps,
    'target_weight' => $targetWeight,
    'updated_at' => now()
], 'routine_id = ? AND exercise_id = ?', [$routineId, $exerciseId]);

redirect("/routines/edit?id=$routineId", 'Exercise updated!');
