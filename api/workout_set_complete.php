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
    header('Location: /workouts/create?error=csrf');
    exit;
}

requireAuth();
$db = getDB();

// Sanitize inputs
$setId = Security::sanitizeInt($_POST['set_id'] ?? 0, 0, 1);
$weight = Security::sanitizeFloat($_POST['weight'] ?? 0, 0, 0, 5000);
$reps = Security::sanitizeInt($_POST['reps'] ?? 0, 0, 0, 1000);

if ($setId <= 0) {
    http_response_code(400);
    header('Location: /workouts/create?error=invalid_set');
    exit;
}

// Verify set belongs to user's active workout
$stmt = $db->prepare("
    SELECT ws.id FROM workout_sets ws 
    JOIN workouts w ON ws.workout_id = w.id 
    WHERE ws.id = ? AND w.user_id = ? AND w.ended_at IS NULL
");
$stmt->execute([$setId, $_SESSION['user_id']]);

if (!$stmt->fetch()) {
    http_response_code(403);
    header('Location: /workouts/create?error=unauthorized');
    exit;
}

// Update set with weight, reps, and mark as completed
$now = round(microtime(true) * 1000);
$stmt = $db->prepare("
    UPDATE workout_sets 
    SET weight = ?, reps = ?, completed_at = ?, updated_at = ? 
    WHERE id = ?
");
$stmt->execute([$weight, $reps, $now, $now, $setId]);

header('Location: /workouts/create');
exit;
