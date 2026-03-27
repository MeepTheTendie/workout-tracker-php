<?php
/**
 * Add Set Action
 * For freestyle workout logging
 */

requireCsrf();

$userId = currentUserId();
$exerciseId = intParam($_POST['exercise_id'] ?? 0);
$reps = intParam($_POST['reps'] ?? 0);
$weight = floatParam($_POST['weight'] ?? 0);

if ($exerciseId <= 0 || $reps <= 0) {
    redirect('/workouts/log', 'Invalid exercise or reps', 'error');
}

// Get active workout
$workout = dbFetchOne(
    "SELECT id FROM workouts WHERE user_id = ? AND ended_at IS NULL",
    [$userId]
);

if (!$workout) {
    redirect('/workouts/log', 'No active workout', 'error');
}

// Get next set number for this exercise
$existingSets = dbFetchOne(
    "SELECT COUNT(*) as count FROM workout_sets WHERE workout_id = ? AND exercise_id = ?",
    [$workout['id'], $exerciseId]
);
$setNumber = ($existingSets['count'] ?? 0) + 1;

// Insert set as completed (freestyle style)
dbInsert('workout_sets', [
    'workout_id' => $workout['id'],
    'exercise_id' => $exerciseId,
    'set_number' => $setNumber,
    'reps' => $reps,
    'weight' => $weight,
    'completed_at' => now(),
    'created_at' => now(),
    'updated_at' => now()
]);

redirect('/workouts/log', 'Set added!');
