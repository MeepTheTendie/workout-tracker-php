<?php
/**
 * Workout Tracker - Router
 */

require_once __DIR__ . '/includes/config.php';

$path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$path = trim($path, '/');

// Route handling
switch ($path) {
    case '':
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
        
    // API routes
    case 'api/login':
        require __DIR__ . '/api/login.php';
        break;
        
    case 'api/workout/start':
        requireAuth();
        require __DIR__ . '/api/workout_start.php';
        break;
        
    case 'api/workout/set':
        requireAuth();
        require __DIR__ . '/api/workout_set.php';
        break;
        
    case 'api/workout/finish':
        requireAuth();
        require __DIR__ . '/api/workout_finish.php';
        break;
        
    default:
        http_response_code(404);
        echo '<h1>404 - Not Found</h1><a href="/dashboard">Go Home</a>';
        break;
}
