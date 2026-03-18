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
    header('Location: /dashboard?error=csrf');
    exit;
}

requireAuth();
$db = getDB();

// Create new workout
$stmt = $db->prepare("INSERT INTO workouts (user_id, started_at, notes, created_at, updated_at) VALUES (?, ?, '', ?, ?)");
$now = round(microtime(true) * 1000);
$stmt->execute([$_SESSION['user_id'], $now, $now, $now]);

header('Location: /workouts/create');
exit;
