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

// Get active workout
$stmt = $db->prepare("SELECT * FROM workouts WHERE user_id = ? AND ended_at IS NULL ORDER BY started_at DESC LIMIT 1");
$stmt->execute([$_SESSION['user_id']]);
$workout = $stmt->fetch();

if ($workout) {
    $now = round(microtime(true) * 1000);
    $stmt = $db->prepare("UPDATE workouts SET ended_at = ?, updated_at = ? WHERE id = ?");
    $stmt->execute([$now, $now, $workout['id']]);
}

header('Location: /dashboard');
exit;
