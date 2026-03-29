<?php
/**
 * Workout Tracker - Main Router
 * 
 * Architecture:
 * - GET requests → Pages (display HTML)
 * - POST requests → Actions (process forms, redirect)
 * - No mixed AJAX - pure server-side
 */

require_once __DIR__ . '/includes/bootstrap.php';

// Get path
$path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$path = trim($path, '/');
$method = $_SERVER['REQUEST_METHOD'];

// Rate limiting
if (!checkRateLimit('default', 120, 60)) {
    http_response_code(429);
    die('Too many requests. Please try again later.');
}

// Root redirect
if ($path === '') {
    redirect(isLoggedIn() ? '/dashboard' : '/login');
}

// Route definitions
// Format: 'path' => ['method' => 'GET|POST', 'auth' => bool, 'file' => 'path/to/file.php']
$routes = [
    // Auth - Pages (GET)
    'login' => ['method' => 'GET', 'auth' => false, 'file' => 'pages/login.php'],
    
    // Auth - Actions (POST)
    'action/auth/login' => ['method' => 'POST', 'auth' => false, 'file' => 'actions/auth/login.php'],
    'action/auth/logout' => ['method' => 'POST', 'auth' => true, 'file' => 'actions/auth/logout.php'],
    
    // Dashboard
    'dashboard' => ['method' => 'GET', 'auth' => true, 'file' => 'pages/dashboard.php'],
    
    // Workouts - Pages (GET)
    'workouts/log' => ['method' => 'GET', 'auth' => true, 'file' => 'pages/workouts/log.php'],
    'workouts/history' => ['method' => 'GET', 'auth' => true, 'file' => 'pages/workouts/history.php'],
    'workouts/view' => ['method' => 'GET', 'auth' => true, 'file' => 'pages/workouts/view.php'],
    
    // Workouts - Actions (POST)
    'action/workouts/start' => ['method' => 'POST', 'auth' => true, 'file' => 'actions/workouts/start.php'],
    'action/workouts/add-set' => ['method' => 'POST', 'auth' => true, 'file' => 'actions/workouts/add-set.php'],
    'action/workouts/complete-set' => ['method' => 'POST', 'auth' => true, 'file' => 'actions/workouts/complete-set.php'],
    'action/workouts/edit-set' => ['method' => 'POST', 'auth' => true, 'file' => 'actions/workouts/edit-set.php'],
    'action/workouts/finish' => ['method' => 'POST', 'auth' => true, 'file' => 'actions/workouts/finish.php'],
    
    // Routines - Pages (GET)
    'routines' => ['method' => 'GET', 'auth' => true, 'file' => 'pages/routines/list.php'],
    'routines/create' => ['method' => 'GET', 'auth' => true, 'file' => 'pages/routines/create.php'],
    'routines/edit' => ['method' => 'GET', 'auth' => true, 'file' => 'pages/routines/edit.php'],
    
    // Routines - Actions (POST)
    'action/routines/create' => ['method' => 'POST', 'auth' => true, 'file' => 'actions/routines/create.php'],
    'action/routines/update' => ['method' => 'POST', 'auth' => true, 'file' => 'actions/routines/update.php'],
    'action/routines/delete' => ['method' => 'POST', 'auth' => true, 'file' => 'actions/routines/delete.php'],
    'action/routines/start' => ['method' => 'POST', 'auth' => true, 'file' => 'actions/routines/start.php'],
    'action/routines/add-exercise' => ['method' => 'POST', 'auth' => true, 'file' => 'actions/routines/add-exercise.php'],
    'action/routines/remove-exercise' => ['method' => 'POST', 'auth' => true, 'file' => 'actions/routines/remove-exercise.php'],
    
    // Stats
    'stats' => ['method' => 'GET', 'auth' => true, 'file' => 'pages/stats.php'],
    
    // Goals
    'goals' => ['method' => 'GET', 'auth' => true, 'file' => 'pages/goals.php'],
    
    // PRs
    'prs' => ['method' => 'GET', 'auth' => true, 'file' => 'pages/prs.php'],
    
    // Cardio
    'cardio' => ['method' => 'GET', 'auth' => true, 'file' => 'pages/cardio.php'],
    'action/cardio/add' => ['method' => 'POST', 'auth' => true, 'file' => 'actions/cardio/add.php'],
    
    // Export
    'export' => ['method' => 'GET', 'auth' => true, 'file' => 'pages/export.php'],
    
    // Workout name update
    'action/workouts/update-name' => ['method' => 'POST', 'auth' => true, 'file' => 'actions/workouts/update-name.php'],
];

// Find route
$route = $routes[$path] ?? null;

if (!$route) {
    http_response_code(404);
    renderPage('404', function() {
        echo '<h1>404 - Page Not Found</h1>';
        echo '<p><a href="/dashboard">Go Home</a></p>';
    }, true);
    exit;
}

// Check method
if ($route['method'] !== $method) {
    http_response_code(405);
    die('Method not allowed');
}

// Check auth
if ($route['auth'] && !isLoggedIn()) {
    $_SESSION['redirect_after_login'] = '/' . $path;
    redirect('/login');
}

// Redirect logged-in users from login page
if (!$route['auth'] && $path === 'login' && isLoggedIn()) {
    redirect('/dashboard');
}

// Execute route
$file = __DIR__ . '/' . $route['file'];
if (!file_exists($file)) {
    error_log("Route file missing: $file");
    http_response_code(500);
    die('Configuration error');
}

require_once $file;
