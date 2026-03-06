<?php

require_once __DIR__ . '/config.php';
require_once __DIR__ . '/lib/Database.php';

$db = getDB();

$exercises = [
    ['name' => 'Bench Press', 'category' => 'Chest'],
    ['name' => 'Incline Bench Press', 'category' => 'Chest'],
    ['name' => 'Dumbbell Fly', 'category' => 'Chest'],
    ['name' => 'Squat', 'category' => 'Legs'],
    ['name' => 'Leg Press', 'category' => 'Legs'],
    ['name' => 'Romanian Deadlift', 'category' => 'Legs'],
    ['name' => 'Leg Curl', 'category' => 'Legs'],
    ['name' => 'Calf Raise', 'category' => 'Legs'],
    ['name' => 'Deadlift', 'category' => 'Back'],
    ['name' => 'Pull Up', 'category' => 'Back'],
    ['name' => 'Lat Pulldown', 'category' => 'Back'],
    ['name' => 'Barbell Row', 'category' => 'Back'],
    ['name' => 'Overhead Press', 'category' => 'Shoulders'],
    ['name' => 'Lateral Raise', 'category' => 'Shoulders'],
    ['name' => 'Face Pull', 'category' => 'Shoulders'],
    ['name' => 'Bicep Curl', 'category' => 'Arms'],
    ['name' => 'Hammer Curl', 'category' => 'Arms'],
    ['name' => 'Tricep Pushdown', 'category' => 'Arms'],
    ['name' => 'Skull Crusher', 'category' => 'Arms'],
    ['name' => 'Plank', 'category' => 'Core'],
    ['name' => 'Cable Crunch', 'category' => 'Core'],
];

$stmt = $db->prepare("INSERT OR IGNORE INTO exercises (name, category) VALUES (?, ?)");
foreach ($exercises as $ex) {
    $stmt->execute([$ex['name'], $ex['category']]);
}

echo "Seeded " . count($exercises) . " exercises\n";
