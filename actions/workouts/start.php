<?php
/**
 * Start Workout Action
 * 
 * Supports workout naming and templates
 */

requireCsrf();

$userId = currentUserId();

// Check for existing active workout
$active = dbFetchOne(
    "SELECT id FROM workouts WHERE user_id = ? AND ended_at IS NULL",
    [$userId]
);

if ($active) {
    redirect('/workouts/log', 'You already have an active workout', 'error');
}

// Get workout name from form or use default
$workoutName = isset($_POST['workout_name']) ? trim($_POST['workout_name']) : '';

// If starting from a routine, we could set a default name based on routine
$routineId = isset($_POST['routine_id']) ? intval($_POST['routine_id']) : null;
if ($routineId && empty($workoutName)) {
    $routine = dbFetchOne("SELECT name FROM routines WHERE id = ? AND user_id = ?", [$routineId, $userId]);
    if ($routine) {
        $workoutName = $routine['name'];
    }
}

// Create new workout
$workoutId = dbInsert('workouts', [
    'user_id' => $userId,
    'started_at' => now(),
    'created_at' => now(),
    'updated_at' => now(),
    'notes' => $workoutName
]);

$message = $workoutName ? "Started: $workoutName" : 'Workout started!';
redirect('/workouts/log', $message);
