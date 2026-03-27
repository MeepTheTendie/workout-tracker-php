<?php
/**
 * Create Routine Action
 */

requireCsrf();

$userId = currentUserId();
$name = stringParam($_POST['name'] ?? '');
$description = stringParam($_POST['description'] ?? '');

if (empty($name)) {
    redirect('/routines/create', 'Routine name is required', 'error');
}

$routineId = dbInsert('routines', [
    'user_id' => $userId,
    'name' => $name,
    'description' => $description,
    'created_at' => now(),
    'updated_at' => now()
]);

redirect("/routines/edit?id=$routineId", 'Routine created! Add exercises below.');
