<?php
/**
 * Add Exercise to Routine Action
 */

requireCsrf();

$userId = currentUserId();
$routineId = intParam($_POST['routine_id'] ?? 0);
$exerciseId = intParam($_POST['exercise_id'] ?? 0);
$targetSets = intParam($_POST['target_sets'] ?? 3, 3, 1, 20);
$targetReps = intParam($_POST['target_reps'] ?? 10, 10, 1, 100);
$targetWeight = floatParam($_POST['target_weight'] ?? 0, 0, 0, 2000);

if ($routineId <= 0 || $exerciseId <= 0) {
    redirect('/routines', 'Invalid data', 'error');
}

// Verify routine ownership
if (!ownsResource('routines', $routineId)) {
    redirect('/routines', 'Not authorized', 'error');
}

// Get next order index
$lastOrder = dbFetchOne(
    "SELECT MAX(order_index) as max_order FROM routine_exercises WHERE routine_id = ?",
    [$routineId]
);
$orderIndex = ($lastOrder['max_order'] ?? 0) + 1;

dbInsert('routine_exercises', [
    'routine_id' => $routineId,
    'exercise_id' => $exerciseId,
    'order_index' => $orderIndex,
    'target_sets' => $targetSets,
    'target_reps' => $targetReps,
    'target_weight' => $targetWeight > 0 ? $targetWeight : null,
    'created_at' => now(),
    'updated_at' => now()
]);

redirect("/routines/edit?id=$routineId", 'Exercise added!');
