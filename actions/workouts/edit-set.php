<?php
/**
 * Edit Set Action
 * Update reps/weight for an existing set
 */

requireCsrf();

$userId = currentUserId();
$setId = intParam($_POST['set_id'] ?? 0);
$reps = intParam($_POST['reps'] ?? 0);
$weight = floatParam($_POST['weight'] ?? 0);

if ($setId <= 0 || $reps <= 0) {
    redirect('/workouts/log', 'Invalid set or reps', 'error');
}

// Verify the set belongs to user's active workout
$set = dbFetchOne(
    "SELECT ws.id 
     FROM workout_sets ws
     JOIN workouts w ON ws.workout_id = w.id
     WHERE ws.id = ? AND w.user_id = ? AND w.ended_at IS NULL",
    [$setId, $userId]
);

if (!$set) {
    redirect('/workouts/log', 'Set not found or workout already finished', 'error');
}

// Update the set
dbUpdate('workout_sets', 
    ['reps' => $reps, 'weight' => $weight, 'updated_at' => now()],
    'id = ?',
    [$setId]
);

redirect('/workouts/log', 'Set updated!');
