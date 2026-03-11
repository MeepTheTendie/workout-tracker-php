<?php

require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../lib/Database.php';

if (!isLoggedIn()) {
    http_response_code(401);
    jsonResponse(['error' => 'Unauthorized']);
}

$method = $_SERVER['REQUEST_METHOD'];
$action = $_GET['action'] ?? '';

if ($method === 'GET' && $action === 'stats') {
    $db = getDB();
    
    $workouts = $db->query("SELECT * FROM workouts")->fetchAll(PDO::FETCH_ASSOC);
    $sets = $db->query("SELECT * FROM workout_sets")->fetchAll(PDO::FETCH_ASSOC);
    $exercises = $db->query("SELECT * FROM exercises")->fetchAll(PDO::FETCH_ASSOC);
    
    $totalWorkouts = count($workouts);
    $totalVolume = array_reduce($sets, fn($sum, $s) => $sum + (($s['weight'] ?? 0) * ($s['reps'] ?? 0)), 0);
    
    $workoutsWithDuration = array_filter($workouts, fn($w) => $w['ended_at'] && $w['started_at']);
    $avgDuration = count($workoutsWithDuration) > 0 
        ? array_reduce($workoutsWithDuration, fn($sum, $w) => $sum + ($w['ended_at'] - $w['started_at']) / 60000, 0) / count($workoutsWithDuration)
        : 0;
    
    $workoutDates = array_values(array_unique(array_map(fn($w) => date('Y-m-d', $w['started_at'] / 1000), $workouts)));
    rsort($workoutDates);
    
    $streak = 0;
    $today = date('Y-m-d');
    
    foreach ($workoutDates as $i => $date) {
        $diff = (strtotime($today) - strtotime($date)) / 86400;
        if ($i === 0 && $diff <= 1) {
            $streak = 1;
        } elseif ($diff === $i || ($i === 0 && $diff === 0)) {
            $streak = $i + 1;
        } else {
            break;
        }
    }
    
    $volumeByExercise = [];
    foreach ($exercises as $ex) {
        $exSets = array_filter($sets, fn($s) => $s['exercise_id'] == $ex['id']);
        $volume = array_reduce($exSets, fn($sum, $s) => $sum + (($s['weight'] ?? 0) * ($s['reps'] ?? 0)), 0);
        if ($volume > 0) {
            $volumeByExercise[] = [
                'exercise_id' => $ex['id'],
                'exercise_name' => $ex['name'],
                'total_sets' => count($exSets),
                'volume' => $volume
            ];
        }
    }
    usort($volumeByExercise, fn($a, $b) => $b['volume'] - $a['volume']);
    
    $thirtyDaysAgo = (time() - 30 * 86400) * 1000;
    $recentWorkouts = array_filter($workouts, fn($w) => $w['started_at'] >= $thirtyDaysAgo);
    $recentWorkoutIds = array_column($recentWorkouts, 'id');
    $recentSets = array_filter($sets, fn($s) => in_array($s['workout_id'], $recentWorkoutIds));
    $recentVolume = array_reduce($recentSets, fn($sum, $s) => $sum + (($s['weight'] ?? 0) * ($s['reps'] ?? 0)), 0);
    
    jsonResponse([
        'totalWorkouts' => $totalWorkouts,
        'totalVolume' => $totalVolume,
        'avgDuration' => round($avgDuration),
        'streak' => $streak,
        'volumeByExercise' => $volumeByExercise,
        'recentVolume' => $recentVolume,
        'recentWorkouts' => count($recentWorkouts)
    ]);
}

if ($method === 'GET' && $action === 'bodyweight') {
    $db = getDB();
    $stmt = $db->query("SELECT * FROM body_weight_logs ORDER BY date DESC");
    jsonResponse($stmt->fetchAll(PDO::FETCH_ASSOC));
}

if ($method === 'POST' && $action === 'bodyweight') {
    requireMethod('POST');
    $data = json_decode(file_get_contents('php://input'), true);
    
    if (empty($data['weight'])) {
        http_response_code(400);
        jsonResponse(['error' => 'weight required']);
    }
    
    $db = getDB();
    $stmt = $db->prepare("INSERT INTO body_weight_logs (date, weight, notes, created_at) VALUES (?, ?, ?, ?)");
    $stmt->execute([
        $data['date'] ?? time() * 1000,
        $data['weight'],
        $data['notes'] ?? null,
        time() * 1000
    ]);
    
    jsonResponse(['id' => $db->lastInsertId()]);
}
