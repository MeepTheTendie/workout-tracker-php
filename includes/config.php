<?php
/**
 * Workout Tracker - Configuration
 */

// Database config
define('DB_HOST', 'localhost');
define('DB_NAME', 'workout_tracker');
define('DB_USER', 'meep');
define('DB_PASS', 'GrrMeep#5Dude');

// Security
define('APP_PASSWORD_HASH', '$2y$12$8Nj7q6rShrAoCmHJDmz6wu/8ygnT2K9Y3S0a0NgCPXv2NzAblkcDa'); // GrrMeep#5Dude

// Session
session_start();
if (!isset($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// HTML escape helper
function h($str) {
    return htmlspecialchars($str ?? '', ENT_QUOTES, 'UTF-8');
}

// DB connection
function getDB() {
    static $db = null;
    if ($db === null) {
        $db = new PDO('mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=utf8mb4', DB_USER, DB_PASS);
        $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $db->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
    }
    return $db;
}

// Auth check
function requireAuth() {
    if (!isset($_SESSION['user_id'])) {
        header('Location: /login');
        exit;
    }
}

function isAuth() {
    return isset($_SESSION['user_id']);
}

function getUser() {
    if (!isAuth()) return null;
    $db = getDB();
    $stmt = $db->prepare("SELECT * FROM users WHERE id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    return $stmt->fetch();
}
