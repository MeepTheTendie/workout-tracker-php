<?php

require_once __DIR__ . '/../config.php';

function initDatabase() {
    $db = getDB();
    
    $db->exec("CREATE TABLE IF NOT EXISTS exercises (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        name TEXT NOT NULL,
        category TEXT NOT NULL
    )");
    
    $db->exec("CREATE TABLE IF NOT EXISTS workouts (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        routine_id INTEGER,
        started_at INTEGER NOT NULL,
        ended_at INTEGER,
        notes TEXT,
        FOREIGN KEY (routine_id) REFERENCES routines(id)
    )");
    
    $db->exec("CREATE TABLE IF NOT EXISTS workout_sets (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        workout_id INTEGER NOT NULL,
        exercise_id INTEGER NOT NULL,
        set_number INTEGER NOT NULL,
        reps INTEGER,
        weight REAL,
        completed_at INTEGER NOT NULL,
        FOREIGN KEY (workout_id) REFERENCES workouts(id),
        FOREIGN KEY (exercise_id) REFERENCES exercises(id)
    )");
    
    $db->exec("CREATE TABLE IF NOT EXISTS routines (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        name TEXT NOT NULL,
        description TEXT
    )");
    
    $db->exec("CREATE TABLE IF NOT EXISTS routine_exercises (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        routine_id INTEGER NOT NULL,
        exercise_id INTEGER NOT NULL,
        order_index INTEGER NOT NULL,
        target_sets INTEGER,
        target_reps INTEGER,
        target_weight REAL,
        FOREIGN KEY (routine_id) REFERENCES routines(id),
        FOREIGN KEY (exercise_id) REFERENCES exercises(id)
    )");
    
    $db->exec("CREATE TABLE IF NOT EXISTS goals (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        exercise_id INTEGER NOT NULL,
        target_weight REAL NOT NULL,
        target_reps INTEGER NOT NULL,
        deadline INTEGER,
        completed INTEGER DEFAULT 0,
        created_at INTEGER NOT NULL,
        FOREIGN KEY (exercise_id) REFERENCES exercises(id)
    )");
    
    $db->exec("CREATE TABLE IF NOT EXISTS body_weight_logs (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        date INTEGER NOT NULL,
        weight REAL NOT NULL,
        notes TEXT,
        created_at INTEGER NOT NULL
    )");
    
    $db->exec("CREATE INDEX IF NOT EXISTS idx_exercises_name ON exercises(name)");
    $db->exec("CREATE INDEX IF NOT EXISTS idx_workouts_started ON workouts(started_at)");
    $db->exec("CREATE INDEX IF NOT EXISTS idx_workout_sets_workout ON workout_sets(workout_id)");
    $db->exec("CREATE INDEX IF NOT EXISTS idx_workout_sets_exercise ON workout_sets(exercise_id)");
    $db->exec("CREATE INDEX IF NOT EXISTS idx_routine_exercises_routine ON routine_exercises(routine_id)");
    $db->exec("CREATE INDEX IF NOT EXISTS idx_goals_exercise ON goals(exercise_id)");
    $db->exec("CREATE INDEX IF NOT EXISTS idx_body_weight_logs_date ON body_weight_logs(date)");
}

initDatabase();
