<?php
require_once __DIR__ . '/../includes/config.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    exit;
}

// Validate CSRF token
$csrfToken = $_POST['csrf_token'] ?? '';
if (!Security::validateCsrfToken($csrfToken)) {
    http_response_code(403);
    header('Location: /routines?error=csrf');
    exit;
}

requireAuth();
$db = getDB();

// Sanitize routine ID
$routineId = Security::sanitizeInt($_POST['routine_id'] ?? 0, 0, 1);

if ($routineId <= 0) {
    header('Location: /routines');
    exit;
}

// Verify routine belongs to user
$stmt = $db->prepare("SELECT * FROM routines WHERE id = ? AND user_id = ?");
$stmt->execute([$routineId, $_SESSION['user_id']]);
$routine = $stmt->fetch();

if (!$routine) {
    header('Location: /routines?error=invalid_routine');
    exit;
}

// Get routine exercises
$stmt = $db->prepare("SELECT re.*, e.name as exercise_name FROM routine_exercises re JOIN exercises e ON re.exercise_id = e.id WHERE re.routine_id = ? ORDER BY re.id");
$stmt->execute([$routineId]);
$routineExercises = $stmt->fetchAll();

if (empty($routineExercises)) {
    header('Location: /routines');
    exit;
}

// Create new workout
$now = round(microtime(true) * 1000);
$stmt = $db->prepare("INSERT INTO workouts (user_id, started_at, created_at, updated_at) VALUES (?, ?, ?, ?)");
$stmt->execute([$_SESSION['user_id'], $now, $now, $now]);
$workoutId = $db->lastInsertId();

// Add sets from routine with target weight/reps, but NOT completed
foreach ($routineExercises as $ex) {
    $sets = Security::sanitizeInt($ex['sets'] ?? 1, 1, 1, 100);
    for ($i = 0; $i < $sets; $i++) {
        $stmt = $db->prepare("INSERT INTO workout_sets (workout_id, exercise_id, set_number, reps, weight, completed_at, created_at, updated_at) VALUES (?, ?, ?, ?, ?, NULL, ?, ?)");
        $stmt->execute([
            $workoutId,
            $ex['exercise_id'],
            $i + 1,
            $ex['reps'] ?? null,      // Target reps from routine
            $ex['weight'] ?? null,    // Target weight from routine
            $now,
            $now
        ]);
    }
}

header('Location: /workouts/create');
exit;
