<?php
/**
 * Workout Tracker - Configuration (Security Hardened)
 */

// Apply security headers
require_once __DIR__ . '/Security.php';
Security::applySecurityHeaders();
Security::init();

// Session configuration for security
ini_set('session.cookie_httponly', 1);
ini_set('session.cookie_secure', isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on');
ini_set('session.cookie_samesite', 'Strict');
ini_set('session.use_strict_mode', 1);
ini_set('session.use_only_cookies', 1);
ini_set('session.cookie_lifetime', 0);
ini_set('session.gc_maxlifetime', 3600); // 1 hour

// Start session
session_start();

// Regenerate session ID periodically to prevent session fixation
if (!isset($_SESSION['created'])) {
    $_SESSION['created'] = time();
} else if (time() - $_SESSION['created'] > 1800) { // 30 minutes
    session_regenerate_id(true);
    $_SESSION['created'] = time();
}

// Database credentials - in production, use environment variables
// For now, use the credentials from AGENTS.md
$dbConfig = [
    'host' => 'localhost',
    'database' => 'workout_tracker',
    'username' => 'meep',
    'password' => 'GrrMeep#5Dude',
    'charset' => 'utf8mb4'
];

/**
 * Get database connection
 */
function getDB(): PDO {
    static $db = null;
    if ($db === null) {
        global $dbConfig;
        $dsn = "mysql:host={$dbConfig['host']};dbname={$dbConfig['database']};charset={$dbConfig['charset']}";
        $db = new PDO($dsn, $dbConfig['username'], $dbConfig['password'], [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ]);
    }
    return $db;
}

/**
 * Check if user is authenticated
 */
function isAuth(): bool {
    return isset($_SESSION['user_id']) && $_SESSION['user_id'] > 0;
}

/**
 * Require authentication
 */
function requireAuth(): void {
    if (!isAuth()) {
        header('Location: /login');
        exit;
    }
}

/**
 * Get current user
 */
function getUser(): ?array {
    if (!isAuth()) {
        return null;
    }
    
    $db = getDB();
    $stmt = $db->prepare("SELECT id, name, email, created_at FROM users WHERE id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    return $stmt->fetch() ?: null;
}

/**
 * HTML escaping helper to prevent XSS
 */
function h($str): string {
    return htmlspecialchars($str ?? '', ENT_QUOTES, 'UTF-8');
}

/**
 * JSON output helper with proper headers
 */
function jsonResponse(array $data, int $status = 200): void {
    http_response_code($status);
    header('Content-Type: application/json');
    echo json_encode($data);
    exit;
}
