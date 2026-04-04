<?php
/**
 * Start Routine Action
 * 
 * Converts a routine template into an active workout by:
 * 1. Verifying the routine exists and belongs to the current user
 * 2. Checking no other workout is currently active
 * 3. Creating a new workout record
 * 4. Pre-populating workout_sets from the routine's exercises with target reps/weight
 * 
 * @package WorkoutTracker\Actions
 */

requireCsrf();

/** @var int Current authenticated user's ID */
$userId = currentUserId();

/** @var int The routine ID to start */
$routineId = intParam($_POST['routine_id'] ?? 0);

// Validate routine ID was provided
if ($routineId <= 0) {
    redirect('/routines', 'Invalid routine', 'error');
}

// Verify the routine exists and belongs to this user (IDOR protection)
$routine = dbFetchOne(
    "SELECT * FROM routines WHERE id = ? AND user_id = ?",
    [$routineId, $userId]
);

if (!$routine) {
    redirect('/routines', 'Routine not found', 'error');
}

// Prevent starting a workout if one is already active (race condition protection)
$active = dbFetchOne(
    "SELECT id FROM workouts WHERE user_id = ? AND ended_at IS NULL",
    [$userId]
);

if ($active) {
    redirect('/workouts/log', 'You already have an active workout. Finish it first.', 'error');
}

// Fetch all exercises defined in this routine, ordered by position
$routineExercises = dbFetchAll(
    "SELECT re.*, e.name as exercise_name 
     FROM routine_exercises re 
     JOIN exercises e ON re.exercise_id = e.id 
     WHERE re.routine_id = ? 
     ORDER BY re.order_index",
    [$routineId]
);

// Cannot start a routine with no exercises
if (empty($routineExercises)) {
    redirect("/routines/edit?id=$routineId", 'Add exercises to this routine first', 'error');
}

// Use transaction to ensure atomic creation of workout + all sets
dbBegin();

try {
    // Create the workout record
    $workoutId = dbInsert('workouts', [
        'user_id' => $userId,
        'started_at' => now(),
        'created_at' => now(),
        'updated_at' => now(),
        'notes' => $routine['name']  // Store routine name as workout name
    ]);
    
    // Pre-populate workout sets from routine template
    // Each exercise in routine generates 'target_sets' number of workout_sets
    foreach ($routineExercises as $ex) {
        $sets = $ex['target_sets'] ?? 3;  // Default to 3 sets if not specified
        
        for ($i = 1; $i <= $sets; $i++) {
            dbInsert('workout_sets', [
                'workout_id' => $workoutId,
                'exercise_id' => $ex['exercise_id'],
                'set_number' => $i,
                'reps' => $ex['target_reps'] ?? null,
                'weight' => $ex['target_weight'] ?? null,
                'completed_at' => null,  // NULL = not yet completed
                'created_at' => now(),
                'updated_at' => now()
            ]);
        }
    }
    
    dbCommit();
    
    // Redirect to workout logger with success message
    redirect('/workouts/log', "Started: {$routine['name']}");
    
} catch (Exception $e) {
    // Roll back on any error to maintain data integrity
    dbRollback();
    error_log("Failed to start routine [{$routineId}]: " . $e->getMessage());
    redirect('/routines', 'Error starting routine', 'error');
}
