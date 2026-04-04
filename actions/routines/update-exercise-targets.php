<?php
/**
 * Update Routine Exercise Targets Action
 * 
 * Updates the target sets, reps, and weight for a specific exercise in a routine.
 * This is used after the system suggests a weight increase based on performance.
 * 
 * @package WorkoutTracker\Actions
 * 
 * POST Parameters:
 * - routine_exercise_id: ID of the routine_exercises record to update
 * - target_weight: New target weight (optional)
 * - target_reps: New target reps (optional)
 * - target_sets: New target sets (optional)
 */

requireCsrf();

/** @var int Current authenticated user's ID */
$userId = currentUserId();

/** @var int The routine exercise ID to update */
$routineExerciseId = intParam($_POST['routine_exercise_id'] ?? 0);

/** @var float|null New target weight */
$targetWeight = isset($_POST['target_weight']) ? floatParam($_POST['target_weight']) : null;

/** @var int|null New target reps */
$targetReps = isset($_POST['target_reps']) ? intParam($_POST['target_reps']) : null;

/** @var int|null New target sets */
$targetSets = isset($_POST['target_sets']) ? intParam($_POST['target_sets']) : null;

// Validate required parameters
if ($routineExerciseId <= 0) {
    redirect('/routines', 'Invalid routine exercise', 'error');
}

// Verify ownership of the routine this exercise belongs to
$routineExercise = dbFetchOne(
    "SELECT re.*, r.user_id, r.id as routine_id, e.name as exercise_name
     FROM routine_exercises re
     JOIN routines r ON re.routine_id = r.id
     JOIN exercises e ON re.exercise_id = e.id
     WHERE re.id = ?",
    [$routineExerciseId]
);

if (!$routineExercise) {
    redirect('/routines', 'Routine exercise not found', 'error');
}

if ($routineExercise['user_id'] !== $userId) {
    redirect('/routines', 'Not authorized', 'error');
}

// Build update data - only update fields that were provided
$updateData = [];
$updateFields = [];

if ($targetWeight !== null && $targetWeight >= 0) {
    $updateData['target_weight'] = $targetWeight;
    $updateFields[] = "weight: {$targetWeight}";
}

if ($targetReps !== null && $targetReps > 0) {
    $updateData['target_reps'] = $targetReps;
    $updateFields[] = "reps: {$targetReps}";
}

if ($targetSets !== null && $targetSets > 0) {
    $updateData['target_sets'] = $targetSets;
    $updateFields[] = "sets: {$targetSets}";
}

if (empty($updateData)) {
    redirect("/routines/edit?id={$routineExercise['routine_id']}", 'No changes provided', 'error');
}

// Add timestamp
$updateData['updated_at'] = now();

// Perform the update
dbUpdate(
    'routine_exercises',
    $updateData,
    'id = ?',
    [$routineExerciseId]
);

$changes = implode(', ', $updateFields);
redirect(
    "/routines/edit?id={$routineExercise['routine_id']}", 
    "Updated {$routineExercise['exercise_name']}: {$changes}"
);
