<?php
/**
 * Remove Exercise from Routine Action
 */

requireCsrf();

$userId = currentUserId();
$routineId = intParam($_POST['routine_id'] ?? 0);
$exerciseId = intParam($_POST['exercise_id'] ?? 0);

if ($routineId <= 0 || $exerciseId <= 0) {
    redirect('/routines', 'Invalid data', 'error');
}

// Verify routine ownership
if (!ownsResource('routines', $routineId)) {
    redirect('/routines', 'Not authorized', 'error');
}

dbDelete(
    'routine_exercises',
    'routine_id = ? AND exercise_id = ?',
    [$routineId, $exerciseId]
);

redirect("/routines/edit?id=$routineId", 'Exercise removed');
