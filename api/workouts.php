<?php

try {
    require_once __DIR__ . '/../config.php';
    require_once __DIR__ . '/../lib/Database.php';

    if (!isLoggedIn()) {
        http_response_code(401);
        jsonResponse(['error' => 'Unauthorized']);
    }
} catch (Exception $e) {
    error_log("Fatal error in workouts API: " . $e->getMessage());
    http_response_code(500);
    header('Content-Type: application/json');
    echo json_encode(['error' => 'Server initialization error: ' . $e->getMessage()]);
    exit;
}

$method = $_SERVER['REQUEST_METHOD'];
$action = $_GET['action'] ?? '';

if ($method === 'GET' && $action === 'workouts') {
    $db = getDB();
    $limit = (int)($_GET['limit'] ?? 100);
    $order = $_GET['order'] ?? 'desc';
    
    $dir = $order === 'asc' ? 'ASC' : 'DESC';
    $stmt = $db->prepare("SELECT * FROM workouts ORDER BY started_at $dir LIMIT ?");
    $stmt->bindValue(1, $limit, PDO::PARAM_INT);
    $stmt->execute();
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
    
    try {
        $input = file_get_contents('php://input');
        error_log("POST workouts input: " . $input);
        $data = json_decode($input, true);
        
        if (!$data) {
            throw new Exception('Invalid JSON input: ' . json_last_error_msg());
        }
        
        $db = getDB();
        $stmt = $db->prepare("INSERT INTO workouts (routine_id, started_at, ended_at, notes) VALUES (?, ?, ?, ?)");
        $result = $stmt->execute([
            $data['routine_id'] ?? null,
            $data['started_at'] ?? time() * 1000,
            $data['ended_at'] ?? null,
            $data['notes'] ?? null
        ]);
        
        if (!$result) {
            throw new Exception('Failed to insert workout: ' . print_r($stmt->errorInfo(), true));
        }
        
        $id = $db->lastInsertId();
        error_log("Workout created with ID: " . $id);
        jsonResponse(['id' => $id]);
    } catch (Exception $e) {
        error_log("Error in POST workouts: " . $e->getMessage());
        http_response_code(500);
        jsonResponse(['error' => $e->getMessage()]);
    }
}

if ($method === 'GET' && $action === 'workout') {
    $id = $_GET['id'] ?? null;
    if (!$id || !is_numeric($id)) {
        http_response_code(400);
        jsonResponse(['error' => 'ID required']);
    }
    
    $db = getDB();
    $stmt = $db->prepare("SELECT * FROM workouts WHERE id = ?");
    $stmt->execute([(int)$id]);
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
    
    $workoutId = (int)$data['workout_id'];
    $exerciseId = (int)$data['exercise_id'];
    $setNumber = isset($data['set_number']) ? max(1, (int)$data['set_number']) : 1;
    $reps = isset($data['reps']) ? max(0, (int)$data['reps']) : null;
    $weight = isset($data['weight']) ? max(0, (float)$data['weight']) : null;
    
    $db = getDB();
    $stmt = $db->prepare("INSERT INTO workout_sets (workout_id, exercise_id, set_number, reps, weight, completed_at) VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->execute([
        $workoutId,
        $exerciseId,
        $setNumber,
        $reps,
        $weight,
        $data['completed_at'] ?? time() * 1000
    ]);
    
    jsonResponse(['id' => $db->lastInsertId()]);
}

if ($method === 'DELETE' && $action === 'sets') {
    requireMethod('DELETE');
    $id = $_GET['id'] ?? null;
    
    if (!$id || !is_numeric($id)) {
        http_response_code(400);
        jsonResponse(['error' => 'ID required']);
    }
    
    $db = getDB();
    $stmt = $db->prepare("DELETE FROM workout_sets WHERE id = ?");
    $stmt->execute([(int)$id]);
    
    jsonResponse(['success' => true]);
}
