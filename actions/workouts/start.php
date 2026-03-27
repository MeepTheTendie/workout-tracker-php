<?php
/**
 * Start Workout Action
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

// Create new workout
$workoutId = dbInsert('workouts', [
    'user_id' => $userId,
    'started_at' => now(),
    'created_at' => now(),
    'updated_at' => now(),
    'notes' => ''
]);

redirect('/workouts/log', 'Workout started!');
