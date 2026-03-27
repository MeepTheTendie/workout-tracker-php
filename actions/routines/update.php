<?php
/**
 * Update Routine Action
 */

requireCsrf();

$userId = currentUserId();
$routineId = intParam($_POST['routine_id'] ?? 0);
$name = stringParam($_POST['name'] ?? '');
$description = stringParam($_POST['description'] ?? '');

if ($routineId <= 0 || empty($name)) {
    redirect('/routines', 'Invalid data', 'error');
}

// Verify ownership
if (!ownsResource('routines', $routineId)) {
    redirect('/routines', 'Not authorized', 'error');
}

dbUpdate('routines', [
    'name' => $name,
    'description' => $description,
    'updated_at' => now()
], 'id = ?', [$routineId]);

redirect("/routines/edit?id=$routineId", 'Routine updated!');
