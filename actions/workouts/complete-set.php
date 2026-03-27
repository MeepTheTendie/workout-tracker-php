<?php
/**
 * Complete Set Action
 * For routine-based workouts (pre-populated sets)
 */

requireCsrf();

$userId = currentUserId();
$setId = intParam($_POST['set_id'] ?? 0);
$reps = intParam($_POST['reps'] ?? 0);
$weight = floatParam($_POST['weight'] ?? 0);

if ($setId <= 0 || $reps <= 0) {
    redirect('/workouts/log', 'Invalid set data', 'error');
}

// Verify set belongs to user's active workout and is not completed
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

// Update set
dbUpdate('workout_sets', [
    'reps' => $reps,
    'weight' => $weight,
    'completed_at' => now(),
    'updated_at' => now()
], 'id = ?', [$setId]);

redirect('/workouts/log', 'Set completed!');
