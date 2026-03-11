<?php

// Load .env file if it exists (for local development)
$envFile = __DIR__ . '/.env';
if (file_exists($envFile)) {
    $lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        if (strpos($line, '#') === 0) continue;
        if (strpos($line, '=') === false) continue;
        list($key, $value) = explode('=', $line, 2);
        $key = trim($key);
        $value = trim($value);
        if (!getenv($key)) {
            putenv("$key=$value");
            $_ENV[$key] = $value;
        }
    }
}

// Session security configuration
ini_set('session.cookie_httponly', 1);
ini_set('session.cookie_secure', isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on');
ini_set('session.cookie_samesite', 'Strict');
ini_set('session.use_strict_mode', 1);

// Security headers
header('X-Frame-Options: DENY');
header('X-Content-Type-Options: nosniff');
header('Referrer-Policy: strict-origin-when-cross-origin');
header("Content-Security-Policy: default-src 'self'; img-src 'self' data:; style-src 'self' 'unsafe-inline' https://fonts.googleapis.com; font-src 'self' https://fonts.gstatic.com; script-src 'self' 'unsafe-inline'; connect-src 'self';");

$dbType = getenv('DATABASE_URL') ? 'pgsql' : 'sqlite';

if ($dbType === 'pgsql') {
    $dbUrl = getenv('DATABASE_URL');
    $parsed = parse_url($dbUrl);
    $dsn = sprintf('pgsql:host=%s;port=%s;dbname=%s', 
        $parsed['host'] ?? 'localhost', 
        $parsed['port'] ?? 5432, 
        ltrim($parsed['path'], '/')
    );
    $db = new PDO($dsn, $parsed['user'] ?? null, $parsed['pass'] ?? null);
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    $result = $db->query("SELECT COUNT(*) FROM information_schema.tables WHERE table_schema = 'public' AND table_name = 'exercises'");
    $tableExists = $result->fetchColumn() > 0;
    
    if (!$tableExists) {
        initPostgresDB($db);
    }
    
    define('DB_PATH', null);
} else {
    define('DB_PATH', __DIR__ . '/data/workout.db');
}

define('BASE_URL', '/');

function getDB() {
    static $db = null;
    if ($db === null) {
        $dbUrl = getenv('DATABASE_URL');
        if ($dbUrl) {
            $parsed = parse_url($dbUrl);
            $dsn = sprintf('pgsql:host=%s;port=%s;dbname=%s', 
                $parsed['host'] ?? 'localhost', 
                $parsed['port'] ?? 5432, 
                ltrim($parsed['path'], '/')
            );
            $db = new PDO($dsn, $parsed['user'] ?? null, $parsed['pass'] ?? null);
            $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            initPostgresDB($db);
        } else {
            $db = new PDO('sqlite:' . DB_PATH);
            $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        }
    }
    return $db;
}

function initPostgresDB($db) {
    $db->exec("CREATE TABLE IF NOT EXISTS exercises (
        id SERIAL PRIMARY KEY,
        name TEXT NOT NULL,
        category TEXT NOT NULL
    )");
    
    $db->exec("CREATE TABLE IF NOT EXISTS workouts (
        id SERIAL PRIMARY KEY,
        routine_id INTEGER,
        started_at BIGINT NOT NULL,
        ended_at BIGINT,
        notes TEXT
    )");
    
    $db->exec("CREATE TABLE IF NOT EXISTS workout_sets (
        id SERIAL PRIMARY KEY,
        workout_id INTEGER NOT NULL,
        exercise_id INTEGER NOT NULL,
        set_number INTEGER NOT NULL,
        reps INTEGER,
        weight REAL,
        completed_at BIGINT NOT NULL
    )");
    
    $db->exec("CREATE TABLE IF NOT EXISTS routines (
        id SERIAL PRIMARY KEY,
        name TEXT NOT NULL,
        description TEXT
    )");
    
    $db->exec("CREATE TABLE IF NOT EXISTS routine_exercises (
        id SERIAL PRIMARY KEY,
        routine_id INTEGER NOT NULL,
        exercise_id INTEGER NOT NULL,
        order_index INTEGER NOT NULL,
        target_sets INTEGER,
        target_reps INTEGER,
        target_weight REAL
    )");
    
    $db->exec("CREATE TABLE IF NOT EXISTS goals (
        id SERIAL PRIMARY KEY,
        exercise_id INTEGER NOT NULL,
        target_weight REAL NOT NULL,
        target_reps INTEGER NOT NULL,
        deadline BIGINT,
        completed BOOLEAN DEFAULT FALSE,
        created_at BIGINT NOT NULL
    )");
    
    $db->exec("CREATE TABLE IF NOT EXISTS body_weight_logs (
        id SERIAL PRIMARY KEY,
        date BIGINT NOT NULL,
        weight REAL NOT NULL,
        notes TEXT,
        created_at BIGINT NOT NULL
    )");
    
    $db->exec("CREATE INDEX IF NOT EXISTS idx_exercises_name ON exercises(name)");
    $db->exec("CREATE INDEX IF NOT EXISTS idx_workouts_started ON workouts(started_at)");
    $db->exec("CREATE INDEX IF NOT EXISTS idx_workout_sets_workout ON workout_sets(workout_id)");
    $db->exec("CREATE INDEX IF NOT EXISTS idx_workout_sets_exercise ON workout_sets(exercise_id)");
}

function seedExercisesIfNeeded($db) {
    $result = $db->query("SELECT COUNT(*) FROM exercises");
    $count = $result->fetchColumn();
    if ($count > 0) return;
    
    $exercises = [
        ['Bench Press', 'Chest'], ['Incline Bench Press', 'Chest'], ['Dumbbell Fly', 'Chest'],
        ['Squat', 'Legs'], ['Leg Press', 'Legs'], ['Romanian Deadlift', 'Legs'],
        ['Leg Curl', 'Legs'], ['Calf Raise', 'Legs'],
        ['Deadlift', 'Back'], ['Pull Up', 'Back'], ['Lat Pulldown', 'Back'], ['Barbell Row', 'Back'],
        ['Overhead Press', 'Shoulders'], ['Lateral Raise', 'Shoulders'], ['Face Pull', 'Shoulders'],
        ['Bicep Curl', 'Arms'], ['Hammer Curl', 'Arms'], ['Tricep Pushdown', 'Arms'], ['Skull Crusher', 'Arms'],
        ['Plank', 'Core'], ['Cable Crunch', 'Core'],
    ];
    
    $stmt = $db->prepare("INSERT INTO exercises (name, category) VALUES (?, ?)");
    foreach ($exercises as $ex) {
        $stmt->execute($ex);
    }
}

function initDB() {
    $db = getDB();
    
    if (getenv('DATABASE_URL')) {
        initPostgresDB($db);
    } else {
        $db->exec("CREATE TABLE IF NOT EXISTS exercises (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            name TEXT NOT NULL,
            category TEXT NOT NULL
        )");
        
        $db->exec("CREATE TABLE IF NOT EXISTS workouts (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            routine_id INTEGER,
            started_at INTEGER NOT NULL,
            ended_at INTEGER,
            notes TEXT
        )");
        
        $db->exec("CREATE TABLE IF NOT EXISTS workout_sets (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            workout_id INTEGER NOT NULL,
            exercise_id INTEGER NOT NULL,
            set_number INTEGER NOT NULL,
            reps INTEGER,
            weight REAL,
            completed_at INTEGER NOT NULL
        )");
        
        $db->exec("CREATE TABLE IF NOT EXISTS routines (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            name TEXT NOT NULL,
            description TEXT
        )");
        
        $db->exec("CREATE TABLE IF NOT EXISTS routine_exercises (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            routine_id INTEGER NOT NULL,
            exercise_id INTEGER NOT NULL,
            order_index INTEGER NOT NULL,
            target_sets INTEGER,
            target_reps INTEGER,
            target_weight REAL
        )");
        
        $db->exec("CREATE TABLE IF NOT EXISTS goals (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            exercise_id INTEGER NOT NULL,
            target_weight REAL NOT NULL,
            target_reps INTEGER NOT NULL,
            deadline INTEGER,
            completed INTEGER DEFAULT 0,
            created_at INTEGER NOT NULL
        )");
        
        $db->exec("CREATE TABLE IF NOT EXISTS body_weight_logs (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            date INTEGER NOT NULL,
            weight REAL NOT NULL,
            notes TEXT,
            created_at INTEGER NOT NULL
        )");
        
        $db->exec("CREATE INDEX IF NOT EXISTS idx_exercises_name ON exercises(name)");
        $db->exec("CREATE INDEX IF NOT EXISTS idx_workouts_started ON workouts(started_at)");
        $db->exec("CREATE INDEX IF NOT EXISTS idx_workout_sets_workout ON workout_sets(workout_id)");
        $db->exec("CREATE INDEX IF NOT EXISTS idx_workout_sets_exercise ON workout_sets(exercise_id)");
        $db->exec("CREATE INDEX IF NOT EXISTS idx_routine_exercises_routine ON routine_exercises(routine_id)");
        $db->exec("CREATE INDEX IF NOT EXISTS idx_goals_exercise ON goals(exercise_id)");
        $db->exec("CREATE INDEX IF NOT EXISTS idx_body_weight_logs_date ON body_weight_logs(date)");
        
        seedExercisesIfNeeded($db);
    }
}

initDB();

function jsonResponse($data) {
    header('Content-Type: application/json');
    echo json_encode($data);
    exit;
}

function requireMethod($method) {
    if ($_SERVER['REQUEST_METHOD'] !== $method) {
        http_response_code(405);
        jsonResponse(['error' => 'Method not allowed']);
    }
}

function csrfToken() {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    if (!isset($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function validateCsrfToken($token) {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}

function h($str) {
    return htmlspecialchars($str ?? '', ENT_QUOTES, 'UTF-8');
}

function getPassword() {
    static $password = null;
    if ($password === null) {
        $password = getenv('APP_PASSWORD');
        if (!$password) {
            throw new Exception('APP_PASSWORD environment variable must be set');
        }
    }
    return $password;
}

function isLoggedIn() {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    return !empty($_SESSION['authenticated']);
}

function requireAuth() {
    if (!isLoggedIn()) {
        header('Location: /?page=login');
        exit;
    }
}

function login($password) {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    if ($password === getPassword()) {
        $_SESSION['authenticated'] = true;
        return true;
    }
    return false;
}

function logout() {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    $_SESSION = [];
    session_destroy();
}

function formatVolume($vol) {
    if ($vol >= 1000000) return round($vol / 1000000, 1) . 'M';
    if ($vol >= 1000) return round($vol / 1000) . 'k';
    return $vol;
}
