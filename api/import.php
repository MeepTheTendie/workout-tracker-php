<?php

require_once __DIR__ . '/config.php';
require_once __DIR__ . '/lib/Database.php';

if (!isLoggedIn()) {
    http_response_code(401);
    jsonResponse(['error' => 'Unauthorized']);
}

$method = $_SERVER['REQUEST_METHOD'];
$action = $_GET['action'] ?? '';

if ($method === 'POST' && $action === 'import') {
    requireMethod('POST');
    $data = json_decode(file_get_contents('php://input'), true);
    
    $db = getDB();
    
    $exercises = $data['exercises'] ?? [];
    $workouts = $data['workouts'] ?? [];
    $sets = $data['sets'] ?? [];
    
    $exerciseIdMap = [];
    foreach ($exercises as $ex) {
        $stmt = $db->prepare("INSERT INTO exercises (name, category) VALUES (?, ?)");
        $stmt->execute([$ex['name'], $ex['category'] ?? 'strength']);
        $exerciseIdMap[$ex['id']] = $db->lastInsertId();
    }
    
    $workoutIdMap = [];
    foreach ($workouts as $w) {
        $startedAt = strtotime($w['started_at']) * 1000;
        $endedAt = $w['ended_at'] ? strtotime($w['ended_at']) * 1000 : null;
        
        $stmt = $db->prepare("INSERT INTO workouts (routine_id, started_at, ended_at, notes) VALUES (?, ?, ?, ?)");
        $stmt->execute([null, $startedAt, $endedAt, $w['notes'] ?? null]);
        $workoutIdMap[$w['id']] = $db->lastInsertId();
    }
    
    $importedSets = 0;
    foreach ($sets as $s) {
        if (!isset($exerciseIdMap[$s['exercise_id']]) || !isset($workoutIdMap[$s['workout_id']])) {
            continue;
        }
        
        $completedAt = strtotime($s['completed_at']) * 1000;
        
        $stmt = $db->prepare("INSERT INTO workout_sets (workout_id, exercise_id, set_number, reps, weight, completed_at) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->execute([
            $workoutIdMap[$s['workout_id']],
            $exerciseIdMap[$s['exercise_id']],
            $s['set_number'],
            $s['reps'],
            $s['weight'],
            $completedAt
        ]);
        $importedSets++;
    }
    
    jsonResponse([
        'exercises' => count($exerciseIdMap),
        'workouts' => count($workoutIdMap),
        'sets' => $importedSets
    ]);
}
