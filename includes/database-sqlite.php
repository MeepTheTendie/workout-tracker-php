<?php
/**
 * SQLite Database Connection for Local Testing
 * Drop-in replacement for database.php
 */

function getDB(): PDO
{
    static $db = null;
    
    if ($db === null) {
        $dbPath = __DIR__ . '/../storage/workout_tracker.db';
        
        $isNew = !file_exists($dbPath);
        
        $db = new PDO("sqlite:$dbPath", null, null, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        ]);
        
        // Enable foreign keys
        $db->exec('PRAGMA foreign_keys = ON');
        
        // Initialize schema if new
        if ($isNew) {
            initSQLiteSchema($db);
        }
    }
    
    return $db;
}

function initSQLiteSchema(PDO $db): void
{
    // Users
    $db->exec("CREATE TABLE users (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        name TEXT NOT NULL,
        email TEXT NOT NULL UNIQUE,
        password TEXT NOT NULL,
        created_at INTEGER,
        updated_at INTEGER
    )");
    
    // Exercises
    $db->exec("CREATE TABLE exercises (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        name TEXT NOT NULL,
        category TEXT NOT NULL,
        created_at INTEGER,
        updated_at INTEGER
    )");
    
    // Workouts
    $db->exec("CREATE TABLE workouts (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        user_id INTEGER NOT NULL,
        started_at INTEGER NOT NULL,
        ended_at INTEGER,
        notes TEXT,
        created_at INTEGER,
        updated_at INTEGER
    )");
    
    // Workout Sets
    $db->exec("CREATE TABLE workout_sets (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        workout_id INTEGER NOT NULL,
        exercise_id INTEGER NOT NULL,
        set_number INTEGER NOT NULL,
        reps INTEGER,
        weight REAL,
        completed_at INTEGER,
        created_at INTEGER,
        updated_at INTEGER
    )");
    
    // Routines
    $db->exec("CREATE TABLE routines (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        user_id INTEGER NOT NULL,
        name TEXT NOT NULL,
        description TEXT,
        created_at INTEGER,
        updated_at INTEGER
    )");
    
    // Routine Exercises
    $db->exec("CREATE TABLE routine_exercises (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        routine_id INTEGER NOT NULL,
        exercise_id INTEGER NOT NULL,
        order_index INTEGER DEFAULT 0,
        target_sets INTEGER,
        target_reps INTEGER,
        target_weight REAL,
        created_at INTEGER,
        updated_at INTEGER
    )");
    
    // Seed with default user and exercises
    $now = time() * 1000;
    
    $db->exec("INSERT INTO users (id, name, email, password, created_at, updated_at) 
               VALUES (1, 'Meep', 'meep@workout.local', '\$2y\$12\$8Nj7q6rShrAoCmHJDmz6wu/8ygnT2K9Y3S0a0NgCPXv2NzAblkcDa', $now, $now)");
    
    // Seed exercises (matches production database)
    $exercises = [
        ['Arm Curl', 'Arms'],
        ['Back Extension', 'Back'],
        ['Barbell Row', 'Back'],
        ['Bench Press', 'Chest'],
        ['Bicep Curl', 'Arms'],
        ['Calf Raise', 'Legs'],
        ['Chest Press', 'Chest'],
        ['Converging Chest Press', 'Chest'],
        ['Crunch', 'Core'],
        ['Curl', 'Arms'],
        ['Curl Alt', 'Arms'],
        ['Deadlift', 'Back'],
        ['Decline Press', 'Chest'],
        ['Dip', 'Chest'],
        ['Diverging Lat Pulldown', 'Back'],
        ['Diverging Seated Row', 'Back'],
        ['Dumbbell Fly', 'Chest'],
        ['Extension', 'Other'],
        ['Extension Alt', 'Other'],
        ['Face Pull', 'Shoulders'],
        ['Fly', 'Chest'],
        ['Forearm Curl', 'Arms'],
        ['Hack Squat', 'Legs'],
        ['Hammer Curl', 'Arms'],
        ['Incline Bench Press', 'Chest'],
        ['Lat Pulldown', 'Back'],
        ['Lateral Raise', 'Shoulders'],
        ['Leg Curl', 'Legs'],
        ['Leg Extension', 'Legs'],
        ['Leg Press', 'Legs'],
        ['Low Back - Roc It', 'Back'],
        ['Lunge', 'Legs'],
        ['Lunges', 'Legs'],
        ['MTS High Row', 'Back'],
        ['Overhead Press', 'Shoulders'],
        ['Pec Fly', 'Chest'],
        ['Plank', 'Core'],
        ['Plank Hold', 'Core'],
        ['Preacher Curl', 'Arms'],
        ['Pull', 'Back'],
        ['Pull Up', 'Back'],
        ['Raise', 'Shoulders'],
        ['Romanian Deadlift', 'Legs'],
        ['Rotary Torso', 'Core'],
        ['Seated Dip', 'Chest'],
        ['Shoulder Press', 'Shoulders'],
        ['Shoulder Press - Machine', 'Shoulders'],
        ['Shrug', 'Shoulders'],
        ['Shrugs', 'Shoulders'],
        ['Skull Crusher', 'Arms'],
        ['Squat', 'Legs'],
        ['Tire Flip', 'Other'],
        ['Tire Flips', 'Other'],
        ['Tire Squat', 'Legs'],
        ['Tire Squats', 'Legs'],
        ['Tricep Extensions', 'Arms'],
        ['Tricep Pushdown', 'Arms'],
        ['Twist', 'Core'],
    ];
    
    $stmt = $db->prepare("INSERT INTO exercises (name, category, created_at, updated_at) VALUES (?, ?, ?, ?)");
    foreach ($exercises as $ex) {
        $stmt->execute([$ex[0], $ex[1], $now, $now]);
    }
    
    // Seed a sample routine
    $db->exec("INSERT INTO routines (id, user_id, name, description, created_at, updated_at) 
               VALUES (1, 1, 'Upper Pull Day', 'Back and biceps workout', $now, $now)");
    
    $routineExercises = [
        [1, 18, 1, 3, 10, 130],  // Back Extension
        [1, 9, 2, 3, 10, 70],    // Diverging Seated Row
        [1, 10, 3, 3, 10, 70],   // Lat Pulldown
        [1, 14, 4, 3, 10, 45],   // Bicep Curl
    ];
    
    $stmt = $db->prepare("INSERT INTO routine_exercises 
        (routine_id, exercise_id, order_index, target_sets, target_reps, target_weight, created_at, updated_at) 
        VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
    foreach ($routineExercises as $re) {
        $stmt->execute([$re[0], $re[1], $re[2], $re[3], $re[4], $re[5], $now, $now]);
    }
}

// Same helper functions as MySQL version
function dbQuery(string $sql, array $params = []): PDOStatement
{
    $db = getDB();
    $stmt = $db->prepare($sql);
    $stmt->execute($params);
    return $stmt;
}

function dbFetchOne(string $sql, array $params = []): ?array
{
    $stmt = dbQuery($sql, $params);
    $result = $stmt->fetch();
    return $result ?: null;
}

function dbFetchAll(string $sql, array $params = []): array
{
    $stmt = dbQuery($sql, $params);
    return $stmt->fetchAll();
}

function dbInsert(string $table, array $data): int
{
    $db = getDB();
    
    $columns = implode('", "', array_keys($data));
    $placeholders = implode(', ', array_fill(0, count($data), '?'));
    
    $sql = "INSERT INTO \"$table\" (\"$columns\") VALUES ($placeholders)";
    $stmt = $db->prepare($sql);
    $stmt->execute(array_values($data));
    
    return (int)$db->lastInsertId();
}

function dbUpdate(string $table, array $data, string $where, array $whereParams = []): int
{
    $db = getDB();
    
    $sets = [];
    $values = [];
    
    foreach ($data as $column => $value) {
        $sets[] = "\"$column\" = ?";
        $values[] = $value;
    }
    
    $sql = "UPDATE \"$table\" SET " . implode(', ', $sets) . " WHERE $where";
    $stmt = $db->prepare($sql);
    $stmt->execute(array_merge($values, $whereParams));
    
    return $stmt->rowCount();
}

function dbDelete(string $table, string $where, array $params = []): int
{
    $sql = "DELETE FROM \"$table\" WHERE $where";
    $stmt = dbQuery($sql, $params);
    return $stmt->rowCount();
}

function dbBegin(): void
{
    getDB()->beginTransaction();
}

function dbCommit(): void
{
    getDB()->commit();
}

function dbRollback(): void
{
    getDB()->rollBack();
}
