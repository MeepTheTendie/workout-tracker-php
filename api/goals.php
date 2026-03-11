<?php

require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../lib/Database.php';

$method = $_SERVER['REQUEST_METHOD'];
$action = $_GET['action'] ?? '';

if (!isLoggedIn()) {
    http_response_code(401);
    jsonResponse(['error' => 'Unauthorized']);
}

if ($method === 'GET' && $action === 'goals') {
    $db = getDB();
    $activeOnly = $_GET['activeOnly'] ?? false;
    
    $query = "
        SELECT g.*, e.name as exercise_name,
        (SELECT COALESCE(MAX(ws.weight), 0) FROM workout_sets ws WHERE ws.exercise_id = g.exercise_id) as current_weight
        FROM goals g
        JOIN exercises e ON g.exercise_id = e.id
    ";
    
    if ($activeOnly) {
        $query .= " WHERE g.completed = FALSE";
    }
    
    $query .= " ORDER BY g.created_at DESC";
    
    $stmt = $db->query($query);
    $goals = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    jsonResponse($goals);
}

if ($method === 'POST' && $action === 'goals') {
    requireMethod('POST');
    $data = json_decode(file_get_contents('php://input'), true);
    
    if (empty($data['exercise_id']) || empty($data['target_weight'])) {
        http_response_code(400);
        jsonResponse(['error' => 'exercise_id and target_weight required']);
    }
    
    $exerciseId = (int)$data['exercise_id'];
    $targetWeight = (float)$data['target_weight'];
    $targetReps = isset($data['target_reps']) ? max(1, (int)$data['target_reps']) : 1;
    
    if ($targetWeight <= 0 || $targetWeight > 10000) {
        http_response_code(400);
        jsonResponse(['error' => 'Invalid target_weight']);
    }
    
    $db = getDB();
    $stmt = $db->prepare("INSERT INTO goals (exercise_id, target_weight, target_reps, deadline, created_at) VALUES (?, ?, ?, ?, ?)");
    $stmt->execute([
        $exerciseId,
        $targetWeight,
        $targetReps,
        $data['deadline'] ?? null,
        time() * 1000
    ]);
    
    jsonResponse(['id' => $db->lastInsertId()]);
}

if ($method === 'PATCH' && $action === 'goals') {
    requireMethod('PATCH');
    $data = json_decode(file_get_contents('php://input'), true);
    
    if (empty($data['id']) || !is_numeric($data['id'])) {
        http_response_code(400);
        jsonResponse(['error' => 'ID required']);
    }
    
    $db = getDB();
    $fields = [];
    $params = [];
    
    if (isset($data['completed'])) {
        $fields[] = 'completed = ?';
        $params[] = $data['completed'] ? 1 : 0;
    }
    
    if (empty($fields)) {
        jsonResponse(['success' => true]);
    }
    
    $params[] = $data['id'];
    $stmt = $db->prepare("UPDATE goals SET " . implode(', ', $fields) . " WHERE id = ?");
    $stmt->execute($params);
    
    jsonResponse(['success' => true]);
}

if ($method === 'DELETE' && $action === 'goals') {
    requireMethod('DELETE');
    $id = $_GET['id'] ?? null;
    
    if (!$id || !is_numeric($id)) {
        http_response_code(400);
        jsonResponse(['error' => 'ID required']);
    }
    
    $db = getDB();
    $stmt = $db->prepare("DELETE FROM goals WHERE id = ?");
    $stmt->execute([(int)$id]);
    
    jsonResponse(['success' => true]);
}
