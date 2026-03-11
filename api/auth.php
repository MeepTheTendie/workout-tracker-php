<?php

require_once __DIR__ . '/../config.php';

$method = $_SERVER['REQUEST_METHOD'];
$action = $_GET['action'] ?? '';

if ($method === 'POST' && $action === 'login') {
    $data = json_decode(file_get_contents('php://input'), true);
    
    if (login($data['password'] ?? '')) {
        jsonResponse(['success' => true]);
    } else {
        http_response_code(401);
        jsonResponse(['error' => 'Invalid password']);
    }
}

if ($method === 'POST' && $action === 'logout') {
    logout();
    jsonResponse(['success' => true]);
}

if ($method === 'GET' && $action === 'check') {
    jsonResponse(['authenticated' => isLoggedIn()]);
}
