<?php
require_once __DIR__ . '/../includes/config.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    exit;
}

requireAuth();
$db = getDB();

// Get active workout
$stmt = $db->prepare("SELECT * FROM workouts WHERE user_id = ? AND ended_at IS NULL ORDER BY started_at DESC LIMIT 1");
$stmt->execute([$_SESSION['user_id']]);
$workout = $stmt->fetch();

if (!$workout) {
    header('Location: /workouts/create');
    exit;
}

$workoutId = $workout['id'];
$exerciseId = $_POST['exercise_id'] ?? 0;
$reps = $_POST['reps'] ?? 0;
$weight = $_POST['weight'] ?? 0;

// Get next set number for this exercise
$stmt = $db->prepare("SELECT COUNT(*) as count FROM workout_sets WHERE workout_id = ? AND exercise_id = ?");
$stmt->execute([$workoutId, $exerciseId]);
$setNumber = $stmt->fetch()['count'] + 1;

// Insert set
$now = round(microtime(true) * 1000);
$stmt = $db->prepare("INSERT INTO workout_sets (workout_id, exercise_id, set_number, reps, weight, completed_at, created_at, updated_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
$stmt->execute([$workoutId, $exerciseId, $setNumber, $reps, $weight, $now, $now, $now]);

header('Location: /workouts/create');
exit;
