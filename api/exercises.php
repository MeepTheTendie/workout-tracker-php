<?php

require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../lib/Database.php';

$method = $_SERVER['REQUEST_METHOD'];
$action = $_GET['action'] ?? '';

if ($action !== 'exercises') {
    if (!isLoggedIn()) {
        http_response_code(401);
        jsonResponse(['error' => 'Unauthorized']);
    }
}

if ($method === 'GET' && $action === 'exercises') {
    $db = getDB();
    $stmt = $db->query("SELECT * FROM exercises ORDER BY name");
    $exercises = $stmt->fetchAll(PDO::FETCH_ASSOC);
    jsonResponse($exercises);
}

if ($method === 'POST' && $action === 'exercises') {
    $data = json_decode(file_get_contents('php://input'), true);
    
    if (empty($data['name']) || empty($data['category'])) {
        http_response_code(400);
        jsonResponse(['error' => 'Name and category required']);
    }
    
    $db = getDB();
    $stmt = $db->prepare("INSERT INTO exercises (name, category) VALUES (?, ?)");
    $stmt->execute([$data['name'], $data['category']]);
    
    jsonResponse(['id' => $db->lastInsertId()]);
}

if ($method === 'GET' && $action === 'exercise') {
    $id = $_GET['id'] ?? null;
    if (!$id || !is_numeric($id)) {
        http_response_code(400);
        jsonResponse(['error' => 'ID required']);
    }
    
    $db = getDB();
    $stmt = $db->prepare("SELECT * FROM exercises WHERE id = ?");
    $stmt->execute([(int)$id]);
    $exercise = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$exercise) {
        http_response_code(404);
        jsonResponse(['error' => 'Not found']);
    }
    
    jsonResponse($exercise);
}
