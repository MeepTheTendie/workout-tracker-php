<?php

require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../lib/Database.php';

$method = $_SERVER['REQUEST_METHOD'];
$action = $_GET['action'] ?? '';

if ($method === 'GET' && $action === 'workouts') {
    $db = getDB();
    $limit = $_GET['limit'] ?? 100;
    $order = $_GET['order'] ?? 'desc';
    
    $dir = $order === 'asc' ? 'ASC' : 'DESC';
    $stmt = $db->query("SELECT * FROM workouts ORDER BY started_at $dir LIMIT $limit");
    $workouts = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    foreach ($workouts as &$workout) {
        $stmt = $db->prepare("
            SELECT ws.*, e.name as exercise_name 
            FROM workout_sets ws 
            JOIN exercises e ON ws.exercise_id = e.id 
            WHERE ws.workout_id = ? 
            ORDER BY ws.completed_at
        ");
        $stmt->execute([$workout['id']]);
        $sets = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        $workout['sets'] = $sets;
        $workout['volume'] = array_reduce($sets, function($sum, $s) {
            return $sum + (($s['weight'] ?? 0) * ($s['reps'] ?? 0));
        }, 0);
    }
    
    jsonResponse($workouts);
}

if ($method === 'POST' && $action === 'workouts') {
    requireMethod('POST');
    $data = json_decode(file_get_contents('php://input'), true);
    
    $db = getDB();
    $stmt = $db->prepare("INSERT INTO workouts (routine_id, started_at, ended_at, notes) VALUES (?, ?, ?, ?)");
    $stmt->execute([
        $data['routine_id'] ?? null,
        $data['started_at'] ?? time() * 1000,
        $data['ended_at'] ?? null,
        $data['notes'] ?? null
    ]);
    
    jsonResponse(['id' => $db->lastInsertId()]);
}

if ($method === 'GET' && $action === 'workout') {
    $id = $_GET['id'] ?? null;
    if (!$id) {
        http_response_code(400);
        jsonResponse(['error' => 'ID required']);
    }
    
    $db = getDB();
    $stmt = $db->prepare("SELECT * FROM workouts WHERE id = ?");
    $stmt->execute([$id]);
    $workout = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$workout) {
        http_response_code(404);
        jsonResponse(['error' => 'Not found']);
    }
    
    $stmt = $db->prepare("
        SELECT ws.*, e.name as exercise_name 
        FROM workout_sets ws 
        JOIN exercises e ON ws.exercise_id = e.id 
        WHERE ws.workout_id = ? 
        ORDER BY ws.completed_at
    ");
    $stmt->execute([$id]);
    $sets = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    $workout['sets'] = $sets;
    $workout['volume'] = array_reduce($sets, function($sum, $s) {
        return $sum + (($s['weight'] ?? 0) * ($s['reps'] ?? 0));
    }, 0);
    
    jsonResponse($workout);
}

if ($method === 'PATCH' && $action === 'workouts') {
    requireMethod('PATCH');
    $data = json_decode(file_get_contents('php://input'), true);
    
    if (empty($data['id'])) {
        http_response_code(400);
        jsonResponse(['error' => 'ID required']);
    }
    
    $db = getDB();
    $fields = [];
    $params = [];
    
    if (isset($data['ended_at'])) {
        $fields[] = 'ended_at = ?';
        $params[] = $data['ended_at'];
    }
    if (isset($data['notes'])) {
        $fields[] = 'notes = ?';
        $params[] = $data['notes'];
    }
    
    if (empty($fields)) {
        jsonResponse(['success' => true]);
    }
    
    $params[] = $data['id'];
    $stmt = $db->prepare("UPDATE workouts SET " . implode(', ', $fields) . " WHERE id = ?");
    $stmt->execute($params);
    
    jsonResponse(['success' => true]);
}

if ($method === 'POST' && $action === 'sets') {
    requireMethod('POST');
    $data = json_decode(file_get_contents('php://input'), true);
    
    if (empty($data['workout_id']) || empty($data['exercise_id'])) {
        http_response_code(400);
        jsonResponse(['error' => 'workout_id and exercise_id required']);
    }
    
    $db = getDB();
    $stmt = $db->prepare("INSERT INTO workout_sets (workout_id, exercise_id, set_number, reps, weight, completed_at) VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->execute([
        $data['workout_id'],
        $data['exercise_id'],
        $data['set_number'] ?? 1,
        $data['reps'] ?? null,
        $data['weight'] ?? null,
        $data['completed_at'] ?? time() * 1000
    ]);
    
    jsonResponse(['id' => $db->lastInsertId()]);
}

if ($method === 'DELETE' && $action === 'sets') {
    requireMethod('DELETE');
    $id = $_GET['id'] ?? null;
    
    if (!$id) {
        http_response_code(400);
        jsonResponse(['error' => 'ID required']);
    }
    
    $db = getDB();
    $stmt = $db->prepare("DELETE FROM workout_sets WHERE id = ?");
    $stmt->execute([$id]);
    
    jsonResponse(['success' => true]);
}
