<?php
require_once __DIR__ . '/../includes/config.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    exit;
}

if (!Security::checkRateLimit('login')) {
    http_response_code(429);
    header('Location: /login?error=rate_limited');
    exit;
}

$password = $_POST['password'] ?? '';
$validPassword = 'GrrMeep#5Dude';

if ($password === $validPassword) {
    $db = getDB();
    $stmt = $db->prepare("SELECT id FROM users WHERE id = 1");
    $stmt->execute();
    $user = $stmt->fetch();
    
    if ($user) {
        $_SESSION['user_id'] = 1;
        $_SESSION['created'] = time();
        header('Location: /dashboard');
        exit;
    } else {
        header('Location: /login?error=invalid');
        exit;
    }
} else {
    header('Location: /login?error=invalid');
    exit;
}
