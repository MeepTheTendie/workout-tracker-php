<?php
/**
 * Workout Tracker - Router (Security Hardened)
 */

require_once __DIR__ . '/includes/config.php';

// Rate limiting for all requests
if (!Security::checkRateLimit('default')) {
    Security::rateLimitExceeded();
}
Security::applyRateLimitHeaders('default');

$path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$path = trim($path, '/');
$path = strtok($path, '?');

// Root path - redirect to login or dashboard
if ($path == '') {
    if (isAuth()) {
        header('Location: /dashboard');
    } else {
        header('Location: /login');
    }
    exit;
}

// Whitelist allowed routes
$allowedRoutes = [
    'dashboard',
    'login', 'logout',
    'workouts', 'workouts/create', 'workouts/view',
    'stats', 'prs', 'goals', 'routines',
    'api/login', 'api/workout/start', 'api/workout/set', 
    'api/workout/set/complete', 'api/workout/finish', 'api/routine/start'
];

// Check if route is allowed
if (!in_array($path, $allowedRoutes, true)) {
    http_response_code(404);
    echo '<h1>404 - Not Found</h1><a href=/dashboard>Go Home</a>';
    exit;
}

// Route handling
switch ($path) {
    case 'dashboard':
        requireAuth();
        require __DIR__ . '/pages/dashboard.php';
        break;
        
    case 'login':
        if (isAuth()) {
            header('Location: /dashboard');
            exit;
        }
        require __DIR__ . '/pages/login.php';
        break;
        
    case 'logout':
        session_destroy();
        header('Location: /login');
        exit;
        
    case 'workouts':
        requireAuth();
        require __DIR__ . '/pages/workouts/list.php';
        break;
        
    case 'workouts/create':
        requireAuth();
        require __DIR__ . '/pages/workouts/create.php';
        break;
        
    case 'workouts/view':
        requireAuth();
        $workoutId = Security::sanitizeInt($_GET['id'] ?? 0, 0, 1);
        if ($workoutId === 0) {
            header('Location: /workouts');
            exit;
        }
        $_GET['id'] = $workoutId;
        require __DIR__ . '/pages/workouts/view.php';
        break;
        
    case 'stats':
        requireAuth();
        require __DIR__ . '/pages/stats.php';
        break;
        
    case 'prs':
        requireAuth();
        require __DIR__ . '/pages/prs.php';
        break;
        
    case 'goals':
        requireAuth();
        require __DIR__ . '/pages/goals.php';
        break;
        
    case 'routines':
        requireAuth();
        require __DIR__ . '/pages/routines.php';
        break;
        
    case 'api/login':
        if (!Security::checkRateLimit('login')) {
            Security::rateLimitExceeded();
        }
        require __DIR__ . '/api/login.php';
        break;
        
    case 'api/workout/start':
        requireAuth();
        if (!Security::checkRateLimit('api')) {
            Security::rateLimitExceeded();
        }
        require __DIR__ . '/api/workout_start.php';
        break;
        
    case 'api/workout/set':
        requireAuth();
        if (!Security::checkRateLimit('api')) {
            Security::rateLimitExceeded();
        }
        require __DIR__ . '/api/workout_set.php';
        break;
        
    case 'api/workout/set/complete':
        requireAuth();
        if (!Security::checkRateLimit('api')) {
            Security::rateLimitExceeded();
        }
        require __DIR__ . '/api/workout_set_complete.php';
        break;
        
    case 'api/workout/finish':
        requireAuth();
        if (!Security::checkRateLimit('api')) {
            Security::rateLimitExceeded();
        }
        require __DIR__ . '/api/workout_finish.php';
        break;
        
    case 'api/routine/start':
        requireAuth();
        if (!Security::checkRateLimit('api')) {
            Security::rateLimitExceeded();
        }
        require __DIR__ . '/api/routine_start.php';
        break;
        
    default:
        http_response_code(404);
        echo '<h1>404 - Not Found</h1><a href=/dashboard>Go Home</a>';
        break;
}
