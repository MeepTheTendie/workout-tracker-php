<?php
/**
 * Start Routine Action
 * Creates a new workout with pre-populated sets from routine
 */

requireCsrf();

$userId = currentUserId();
$routineId = intParam($_POST['routine_id'] ?? 0);

if ($routineId <= 0) {
    redirect('/routines', 'Invalid routine', 'error');
}

// Verify ownership
$routine = dbFetchOne(
    "SELECT * FROM routines WHERE id = ? AND user_id = ?",
    [$routineId, $userId]
);

if (!$routine) {
    redirect('/routines', 'Routine not found', 'error');
}

// Check for existing active workout
$active = dbFetchOne(
    "SELECT id FROM workouts WHERE user_id = ? AND ended_at IS NULL",
    [$userId]
);

if ($active) {
    redirect('/workouts/log', 'You already have an active workout. Finish it first.', 'error');
}

// Get routine exercises
$routineExercises = dbFetchAll(
    "SELECT re.*, e.name as exercise_name 
     FROM routine_exercises re 
     JOIN exercises e ON re.exercise_id = e.id 
     WHERE re.routine_id = ? 
     ORDER BY re.order_index",
    [$routineId]
);

if (empty($routineExercises)) {
    redirect("/routines/edit?id=$routineId", 'Add exercises to this routine first', 'error');
}

dbBegin();

try {
    // Create workout
    $workoutId = dbInsert('workouts', [
        'user_id' => $userId,
        'started_at' => now(),
        'created_at' => now(),
        'updated_at' => now(),
        'notes' => $routine['name']
    ]);
    
    // Add pre-populated sets from routine
    foreach ($routineExercises as $ex) {
        $sets = $ex['target_sets'] ?? 3;
        for ($i = 1; $i <= $sets; $i++) {
            dbInsert('workout_sets', [
                'workout_id' => $workoutId,
                'exercise_id' => $ex['exercise_id'],
                'set_number' => $i,
                'reps' => $ex['target_reps'] ?? null,
                'weight' => $ex['target_weight'] ?? null,
                'completed_at' => null, // Not completed yet
                'created_at' => now(),
                'updated_at' => now()
            ]);
        }
    }
    
    dbCommit();
    redirect('/workouts/log', "Started: {$routine['name']}");
} catch (Exception $e) {
    dbRollback();
    error_log("Failed to start routine: " . $e->getMessage());
    redirect('/routines', 'Error starting routine', 'error');
}
