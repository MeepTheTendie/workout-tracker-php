<?php
/**
 * Workout Tracker - Application Bootstrap
 * Loads configuration, initializes session, sets up environment
 */

// Load environment variables
function loadEnv(string $path): void
{
    if (!file_exists($path)) {
        throw new Exception('Environment file not found: ' . $path);
    }
    
    $lines = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        if (strpos($line, '#') === 0) continue;
        if (strpos($line, '=') === false) continue;
        
        list($key, $value) = explode('=', $line, 2);
        $key = trim($key);
        $value = trim($value);
        
        if (!empty($key)) {
            $_ENV[$key] = $value;
            putenv("$key=$value");
        }
    }
}

// Load .env file
try {
    loadEnv(__DIR__ . '/../.env');
} catch (Exception $e) {
    die('Configuration error: ' . $e->getMessage());
}

// Error reporting based on environment
if ($_ENV['APP_ENV'] === 'development') {
    error_reporting(E_ALL);
    ini_set('display_errors', '1');
} else {
    error_reporting(0);
    ini_set('display_errors', '0');
}

// Timezone
date_default_timezone_set('America/Chicago');

// Session configuration
ini_set('session.cookie_httponly', '1');
ini_set('session.cookie_secure', isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? '1' : '0');
ini_set('session.cookie_samesite', 'Strict');
ini_set('session.use_strict_mode', '1');
ini_set('session.use_only_cookies', '1');
ini_set('session.cookie_lifetime', '0');
ini_set('session.gc_maxlifetime', (int)($_ENV['SESSION_LIFETIME'] ?? 3600));

session_name($_ENV['SESSION_NAME'] ?? 'workout_tracker');
session_start();

// Regenerate session ID periodically
if (!isset($_SESSION['created'])) {
    $_SESSION['created'] = time();
} elseif (time() - $_SESSION['created'] > 1800) {
    session_regenerate_id(true);
    $_SESSION['created'] = time();
}

// Security headers
header('X-Frame-Options: DENY');
header('X-Content-Type-Options: nosniff');
header('Referrer-Policy: strict-origin-when-cross-origin');
header("Content-Security-Policy: default-src 'self'; script-src 'self' 'unsafe-inline'; style-src 'self' 'unsafe-inline' https://fonts.googleapis.com; font-src https://fonts.gstatic.com;");

// Require all core files
require_once __DIR__ . '/database.php';
require_once __DIR__ . '/security.php';
require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/helpers.php';
