<?php
require_once __DIR__ . '/../includes/config.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);
    exit;
}

$password = $_POST['password'] ?? '';

if (password_verify($password, APP_PASSWORD_HASH)) {
    $_SESSION['user_id'] = 1; // Default user
    header('Location: /dashboard');
    exit;
} else {
    header('Location: /login?error=1');
    exit;
}
