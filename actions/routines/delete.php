<?php
/**
 * Delete Routine Action
 */

requireCsrf();

$userId = currentUserId();
$routineId = intParam($_POST['routine_id'] ?? 0);

if ($routineId <= 0) {
    redirect('/routines', 'Invalid routine', 'error');
}

// Verify ownership
if (!ownsResource('routines', $routineId)) {
    redirect('/routines', 'Not authorized', 'error');
}

dbBegin();

try {
    // Delete routine exercises first (foreign key)
    dbDelete('routine_exercises', 'routine_id = ?', [$routineId]);
    
    // Delete routine
    dbDelete('routines', 'id = ?', [$routineId]);
    
    dbCommit();
    redirect('/routines', 'Routine deleted');
} catch (Exception $e) {
    dbRollback();
    redirect('/routines', 'Error deleting routine', 'error');
}
