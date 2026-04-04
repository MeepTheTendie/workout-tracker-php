<?php
/**
 * Complete Set Action
 * 
 * Marks a pre-populated workout set as completed with actual reps/weight.
 * Used during routine-based workouts where sets are pre-populated with targets.
 * 
 * @package WorkoutTracker\Actions
 */

requireCsrf();

/** @var int Current authenticated user's ID */
$userId = currentUserId();

/** @var int The workout set ID to complete */
$setId = intParam($_POST['set_id'] ?? 0);

/** @var int Actual reps performed (required) */
$reps = intParam($_POST['reps'] ?? 0);

/** @var float Actual weight used */
$weight = floatParam($_POST['weight'] ?? 0);

// Validate required fields
if ($setId <= 0 || $reps <= 0) {
    redirect('/workouts/log', 'Invalid set data', 'error');
}

// Verify set belongs to user's active workout and is not already completed
// This prevents double-logging and ensures the workout is still in progress
$set = dbFetchOne(
    "SELECT ws.id 
     FROM workout_sets ws 
     JOIN workouts w ON ws.workout_id = w.id 
     WHERE ws.id = ? AND w.user_id = ? AND w.ended_at IS NULL AND ws.completed_at IS NULL",
    [$setId, $userId]
);

if (!$set) {
    redirect('/workouts/log', 'Set not found or already completed', 'error');
}

// Update the set with actual performance data and timestamp
dbUpdate('workout_sets', [
    'reps' => $reps,
    'weight' => $weight,
    'completed_at' => now(),  // Marks set as completed
    'updated_at' => now()
], 'id = ?', [$setId]);

redirect('/workouts/log', 'Set completed!');
