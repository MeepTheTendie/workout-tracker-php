<?php

require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../lib/Database.php';

if (!isLoggedIn()) {
    http_response_code(401);
    jsonResponse(['error' => 'Unauthorized']);
}

$method = $_SERVER['REQUEST_METHOD'];
$action = $_GET['action'] ?? '';

if ($method === 'GET' && $action === 'routines') {
    $db = getDB();
    $stmt = $db->query("SELECT * FROM routines ORDER BY name");
    $routines = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    foreach ($routines as &$routine) {
        $stmt = $db->prepare("
            SELECT re.*, e.name as exercise_name
            FROM routine_exercises re
            JOIN exercises e ON re.exercise_id = e.id
            WHERE re.routine_id = ?
            ORDER BY re.order_index
        ");
        $stmt->execute([$routine['id']]);
        $routine['exercises'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    jsonResponse($routines);
}

if ($method === 'POST' && $action === 'routines') {
    requireMethod('POST');
    $data = json_decode(file_get_contents('php://input'), true);
    
    if (empty($data['name'])) {
        http_response_code(400);
        jsonResponse(['error' => 'Name required']);
    }
    
    $db = getDB();
    $stmt = $db->prepare("INSERT INTO routines (name, description) VALUES (?, ?)");
    $stmt->execute([$data['name'], $data['description'] ?? null]);
    
    jsonResponse(['id' => $db->lastInsertId()]);
}

if ($method === 'GET' && $action === 'routine') {
    $id = $_GET['id'] ?? null;
    if (!$id) {
        http_response_code(400);
        jsonResponse(['error' => 'ID required']);
    }
    
    $db = getDB();
    $stmt = $db->prepare("SELECT * FROM routines WHERE id = ?");
    $stmt->execute([$id]);
    $routine = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$routine) {
        http_response_code(404);
        jsonResponse(['error' => 'Not found']);
    }
    
    $stmt = $db->prepare("
        SELECT re.*, e.name as exercise_name
        FROM routine_exercises re
        JOIN exercises e ON re.exercise_id = e.id
        WHERE re.routine_id = ?
        ORDER BY re.order_index
    ");
    $stmt->execute([$id]);
    $routine['exercises'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    jsonResponse($routine);
}

if ($method === 'DELETE' && $action === 'routines') {
    requireMethod('DELETE');
    $id = $_GET['id'] ?? null;
    
    if (!$id) {
        http_response_code(400);
        jsonResponse(['error' => 'ID required']);
    }
    
    $db = getDB();
    $db->prepare("DELETE FROM routine_exercises WHERE routine_id = ?")->execute([$id]);
    $db->prepare("DELETE FROM routines WHERE id = ?")->execute([$id]);
    
    jsonResponse(['success' => true]);
}

if ($method === 'POST' && $action === 'routine-exercises') {
    requireMethod('POST');
    $data = json_decode(file_get_contents('php://input'), true);
    
    if (empty($data['routine_id']) || empty($data['exercise_id'])) {
        http_response_code(400);
        jsonResponse(['error' => 'routine_id and exercise_id required']);
    }
    
    $db = getDB();
    $stmt = $db->prepare("INSERT INTO routine_exercises (routine_id, exercise_id, order_index, target_sets, target_reps, target_weight) VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->execute([
        $data['routine_id'],
        $data['exercise_id'],
        $data['order_index'] ?? 0,
        $data['target_sets'] ?? null,
        $data['target_reps'] ?? null,
        $data['target_weight'] ?? null
    ]);
    
    jsonResponse(['id' => $db->lastInsertId()]);
}
