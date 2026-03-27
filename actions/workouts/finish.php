<?php
/**
 * Finish Workout Action
 */

requireCsrf();

$userId = currentUserId();

// Get active workout
$workout = dbFetchOne(
    "SELECT id FROM workouts WHERE user_id = ? AND ended_at IS NULL",
    [$userId]
);

if (!$workout) {
    redirect('/workouts/log', 'No active workout', 'error');
}

// Update workout
dbUpdate('workout_sets', [
    'updated_at' => now()
], 'workout_id = ?', [$workout['id']]);

dbUpdate('workouts', [
    'ended_at' => now(),
    'updated_at' => now()
], 'id = ?', [$workout['id']]);

redirect('/dashboard', 'Workout completed! Great job!');
