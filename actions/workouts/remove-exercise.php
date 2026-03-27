<?php
/**
 * Remove Exercise from Active Workout Action
 * Deletes all sets for an exercise from the current workout
 */

requireCsrf();

$userId = currentUserId();
$exerciseId = intParam($_POST['exercise_id'] ?? 0);

if ($exerciseId <= 0) {
    redirect('/workouts/log', 'Invalid exercise', 'error');
}

// Get active workout
$workout = dbFetchOne(
    "SELECT id FROM workouts WHERE user_id = ? AND ended_at IS NULL",
    [$userId]
);

if (!$workout) {
    redirect('/workouts/log', 'No active workout', 'error');
}

// Verify this exercise has sets in the workout
$existingSets = dbFetchOne(
    "SELECT COUNT(*) as count FROM workout_sets WHERE workout_id = ? AND exercise_id = ?",
    [$workout['id'], $exerciseId]
);

if (($existingSets['count'] ?? 0) === 0) {
    redirect('/workouts/log', 'Exercise not found in workout', 'error');
}

// Delete all sets for this exercise in the workout
dbDelete(
    'workout_sets',
    'workout_id = ? AND exercise_id = ?',
    [$workout['id'], $exerciseId]
);

redirect('/workouts/log', 'Exercise removed from workout');
