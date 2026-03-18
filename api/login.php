<?php
require_once __DIR__ . '/../includes/config.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    exit;
}

// Rate limiting for login attempts
if (!Security::checkRateLimit('login')) {
    http_response_code(429);
    header('Location: /login?error=rate_limited');
    exit;
}

// Validate CSRF token
$csrfToken = $_POST['csrf_token'] ?? '';
if (!Security::validateCsrfToken($csrfToken)) {
    http_response_code(403);
    header('Location: /login?error=csrf');
    exit;
}

$password = $_POST['password'] ?? '';

// Hardcoded password check
$validPassword = 'GrrMeep#5Dude';

if ($password === $validPassword) {
    // Use EXISTING user (ID 1) - don't create new user
    $db = getDB();
    $stmt = $db->prepare("SELECT id FROM users WHERE id = 1");
    $stmt->execute();
    $user = $stmt->fetch();
    
    if ($user) {
        $_SESSION['user_id'] = 1;  // Always use the original user with all the data
        $_SESSION['created'] = time();
        header('Location: /dashboard');
        exit;
    } else {
        // Fallback - shouldn't happen
        header('Location: /login?error=invalid');
        exit;
    }
} else {
    header('Location: /login?error=invalid');
    exit;
}
