<?php

require_once __DIR__ . '/config.php';

$exercisesJson = file_get_contents('/tmp/workout-tracker/exercises-import.json');
$workoutsJson = file_get_contents('/tmp/workout-tracker/workouts-import.json');
$setsJson = file_get_contents('/tmp/workout-tracker/sets-import.json');

$exercises = json_decode($exercisesJson, true);
$workouts = json_decode($workoutsJson, true);
$sets = json_decode($setsJson, true);

$db = getDB();

echo "Importing " . count($exercises) . " exercises...\n";
$exerciseIdMap = [];

foreach ($exercises as $ex) {
    $stmt = $db->prepare("INSERT INTO exercises (name, category) VALUES (?, ?)");
    $stmt->execute([$ex['name'], $ex['category'] ?? 'strength']);
    $newId = $db->lastInsertId();
    $exerciseIdMap[$ex['id']] = $newId;
}

echo "Imported " . count($exerciseIdMap) . " exercises\n";

echo "Importing " . count($workouts) . " workouts...\n";
$workoutIdMap = [];

foreach ($workouts as $w) {
    $startedAt = strtotime($w['started_at']) * 1000;
    $endedAt = $w['ended_at'] ? strtotime($w['ended_at']) * 1000 : null;
    
    $stmt = $db->prepare("INSERT INTO workouts (routine_id, started_at, ended_at, notes) VALUES (?, ?, ?, ?)");
    $stmt->execute([null, $startedAt, $endedAt, $w['notes']]);
    $workoutIdMap[$w['id']] = $db->lastInsertId();
}

echo "Imported " . count($workoutIdMap) . " workouts\n";

echo "Importing " . count($sets) . " sets...\n";
$imported = 0;

foreach ($sets as $s) {
    if (!isset($exerciseIdMap[$s['exercise_id']])) {
        echo "Skipping set - no exercise mapping for ID {$s['exercise_id']}\n";
        continue;
    }
    
    if (!isset($workoutIdMap[$s['workout_id']])) {
        echo "Skipping set - no workout mapping for ID {$s['workout_id']}\n";
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
    $imported++;
}

echo "Imported $imported sets\n";

$totalSets = $db->query("SELECT COUNT(*) FROM workout_sets")->fetchColumn();
$totalWorkouts = $db->query("SELECT COUNT(*) FROM workouts")->fetchColumn();
$totalExercises = $db->query("SELECT COUNT(*) FROM exercises")->fetchColumn();

echo "Done! Total: $totalExercises exercises, $totalWorkouts workouts, $totalSets sets\n";
