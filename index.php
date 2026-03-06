<?php

require_once __DIR__ . '/config.php';
require_once __DIR__ . '/lib/Database.php';

$page = $_GET['page'] ?? 'index';
$id = $_GET['id'] ?? null;

function localApi($endpoint, $id = null) {
    $db = getDB();
    
    if ($endpoint === 'stats') {
        $workouts = $db->query("SELECT * FROM workouts")->fetchAll(PDO::FETCH_ASSOC);
        $sets = $db->query("SELECT * FROM workout_sets")->fetchAll(PDO::FETCH_ASSOC);
        
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
        
        return [
            'totalWorkouts' => $totalWorkouts,
            'totalVolume' => $totalVolume,
            'avgDuration' => round($avgDuration),
            'streak' => $streak,
            'volumeByExercise' => [],
            'recentVolume' => 0,
            'recentWorkouts' => 0
        ];
    }
    
    if ($endpoint === 'routines') {
        $stmt = $db->query("SELECT * FROM routines ORDER BY name");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    if ($endpoint === 'goals') {
        $stmt = $db->query("
            SELECT g.*, e.name as exercise_name,
            (SELECT COALESCE(MAX(ws.weight), 0) FROM workout_sets ws WHERE ws.exercise_id = g.exercise_id) as current_weight
            FROM goals g
            JOIN exercises e ON g.exercise_id = e.id
            WHERE g.completed = 0
            ORDER BY g.created_at DESC
        ");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    if ($endpoint === 'exercises') {
        $stmt = $db->query("SELECT * FROM exercises ORDER BY name");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    if ($endpoint === 'workouts') {
        $stmt = $db->query("SELECT * FROM workouts ORDER BY started_at DESC LIMIT 100");
        $workouts = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        foreach ($workouts as &$w) {
            $setsStmt = $db->prepare("
                SELECT ws.*, e.name as exercise_name 
                FROM workout_sets ws 
                JOIN exercises e ON ws.exercise_id = e.id 
                WHERE ws.workout_id = ? 
                ORDER BY ws.completed_at
            ");
            $setsStmt->execute([$w['id']]);
            $sets = $setsStmt->fetchAll(PDO::FETCH_ASSOC);
            $w['sets'] = $sets;
            $w['volume'] = array_reduce($sets, fn($sum, $s) => $sum + (($s['weight'] ?? 0) * ($s['reps'] ?? 0)), 0);
        }
        
        return $workouts;
    }
    
    return null;
}

function formatVolume($vol) {
    if ($vol >= 1000000) return round($vol / 1000000, 1) . 'M';
    if ($vol >= 1000) return round($vol / 1000) . 'k';
    return $vol;
}

$validPages = ['index', 'workout', 'history', 'stats', 'goals', 'routines'];
if (!in_array($page, $validPages)) {
    $page = 'index';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>WORKOUT TRACKER</title>
    <link rel="stylesheet" href="/style.css">
    <link href="https://fonts.googleapis.com/css2?family=Space+Mono:wght@400;700&display=swap" rel="stylesheet">
</head>
<body>
    <div class="app">
        <div class="content">
            <?php include __DIR__ . '/templates/' . $page . '.php'; ?>
        </div>
        
        <nav>
            <a href="/?page=index" class="nav-btn <?= $page === 'index' ? 'active' : '' ?>">HOME</a>
            <a href="/?page=workout" class="nav-btn <?= $page === 'workout' ? 'active' : '' ?>">LOG</a>
            <a href="/?page=history" class="nav-btn <?= $page === 'history' ? 'active' : '' ?>">HISTORY</a>
            <a href="/?page=stats" class="nav-btn <?= $page === 'stats' ? 'active' : '' ?>">STATS</a>
            <a href="/?page=goals" class="nav-btn <?= $page === 'goals' ? 'active' : '' ?>">PRS</a>
        </nav>
    </div>
</body>
</html>
