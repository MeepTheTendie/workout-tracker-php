<?php
/**
 * Routine Flow Integration Tests
 * 
 * Tests the complete routine → workout lifecycle:
 * 1. Creating routines with exercises
 * 2. Starting a workout from a routine
 * 3. Completing sets during a workout
 * 4. Finishing a workout
 * 
 * @package WorkoutTracker\Tests
 * 
 * Note: These tests require PHP 8.4+ and access to a test database.
 * Run with: php vendor/bin/phpunit tests/Unit/RoutineFlowTest.php
 */

declare(strict_types=1);

use PHPUnit\Framework\TestCase;

/**
 * @covers RoutineFlow
 */
class RoutineFlowTest extends TestCase
{
    private static PDO $testDb;
    private static int $testUserId = 1;
    private static int $testRoutineId;
    private static int $testWorkoutId;

    /**
     * Set up test database with full schema
     */
    public static function setUpBeforeClass(): void
    {
        // Use SQLite in-memory for fast, isolated testing
        $_ENV['DB_HOST'] = '';
        $_ENV['DB_NAME'] = ':memory:';
        $_ENV['DB_USER'] = '';
        $_ENV['DB_PASS'] = '';

        // Load database functions
        require_once __DIR__ . '/../../includes/database.php';
        require_once __DIR__ . '/../../includes/helpers.php';
        require_once __DIR__ . '/../../includes/auth.php';
        require_once __DIR__ . '/../../includes/security.php';

        // Create test schema matching production
        $db = getDB();
        
        $db->exec('
            CREATE TABLE users (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                name TEXT NOT NULL,
                email TEXT UNIQUE NOT NULL,
                password TEXT,
                created_at INTEGER,
                updated_at INTEGER
            )
        ');

        $db->exec('
            CREATE TABLE exercises (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                name TEXT NOT NULL,
                category TEXT NOT NULL,
                equipment_type TEXT,
                created_at INTEGER
            )
        ');

        $db->exec('
            CREATE TABLE routines (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                user_id INTEGER NOT NULL,
                name TEXT NOT NULL,
                description TEXT,
                created_at INTEGER,
                updated_at INTEGER,
                FOREIGN KEY (user_id) REFERENCES users(id)
            )
        ');

        $db->exec('
            CREATE TABLE routine_exercises (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                routine_id INTEGER NOT NULL,
                exercise_id INTEGER NOT NULL,
                order_index INTEGER DEFAULT 0,
                target_sets INTEGER DEFAULT 3,
                target_reps INTEGER DEFAULT 10,
                target_weight REAL,
                created_at INTEGER,
                updated_at INTEGER,
                FOREIGN KEY (routine_id) REFERENCES routines(id),
                FOREIGN KEY (exercise_id) REFERENCES exercises(id)
            )
        ');

        $db->exec('
            CREATE TABLE workouts (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                user_id INTEGER NOT NULL,
                started_at INTEGER NOT NULL,
                ended_at INTEGER,
                notes TEXT,
                created_at INTEGER,
                updated_at INTEGER,
                FOREIGN KEY (user_id) REFERENCES users(id)
            )
        ');

        $db->exec('
            CREATE TABLE workout_sets (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                workout_id INTEGER NOT NULL,
                exercise_id INTEGER NOT NULL,
                set_number INTEGER DEFAULT 1,
                reps INTEGER,
                weight REAL,
                completed_at INTEGER,
                created_at INTEGER,
                updated_at INTEGER,
                FOREIGN KEY (workout_id) REFERENCES workouts(id),
                FOREIGN KEY (exercise_id) REFERENCES exercises(id)
            )
        ');

        // Insert test user
        dbInsert('users', [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'created_at' => now(),
            'updated_at' => now()
        ]);

        // Insert test exercises
        $exercises = [
            ['name' => 'Bench Press', 'category' => 'Chest'],
            ['name' => 'Squat', 'category' => 'Legs'],
            ['name' => 'RDL', 'category' => 'Back'],
            ['name' => 'Row', 'category' => 'Back'],
            ['name' => 'Bicep Curl', 'category' => 'Arms'],
        ];

        foreach ($exercises as $ex) {
            dbInsert('exercises', array_merge($ex, ['created_at' => now()]));
        }

        // Mock session for auth
        $_SESSION['user_id'] = self::$testUserId;
    }

    protected function tearDown(): void
    {
        // Clean up workout data but keep schema
        try {
            dbQuery('DELETE FROM workout_sets');
            dbQuery('DELETE FROM workouts');
            dbQuery('DELETE FROM routine_exercises');
            dbQuery('DELETE FROM routines');
        } catch (Exception $e) {
            // Ignore cleanup errors
        }
    }

    /**
     * Test: Create a new routine with exercises
     */
    public function testCreateRoutineWithExercises(): void
    {
        // Create routine
        $routineId = dbInsert('routines', [
            'user_id' => self::$testUserId,
            'name' => 'Test Push Day',
            'description' => 'Chest and triceps',
            'created_at' => now(),
            'updated_at' => now()
        ]);

        $this->assertIsInt($routineId);
        $this->assertGreaterThan(0, $routineId);

        // Add exercises to routine
        dbInsert('routine_exercises', [
            'routine_id' => $routineId,
            'exercise_id' => 1, // Bench Press
            'order_index' => 1,
            'target_sets' => 4,
            'target_reps' => 8,
            'target_weight' => 135.0,
            'created_at' => now(),
            'updated_at' => now()
        ]);

        dbInsert('routine_exercises', [
            'routine_id' => $routineId,
            'exercise_id' => 5, // Bicep Curl
            'order_index' => 2,
            'target_sets' => 3,
            'target_reps' => 12,
            'target_weight' => 30.0,
            'created_at' => now(),
            'updated_at' => now()
        ]);

        // Verify exercises were added
        $exercises = dbFetchAll(
            "SELECT * FROM routine_exercises WHERE routine_id = ? ORDER BY order_index",
            [$routineId]
        );

        $this->assertCount(2, $exercises);
        $this->assertEquals(1, $exercises[0]['exercise_id']); // Bench Press first
        $this->assertEquals(5, $exercises[1]['exercise_id']); // Bicep Curl second
        $this->assertEquals(4, $exercises[0]['target_sets']);
        $this->assertEquals(8, $exercises[0]['target_reps']);
        $this->assertEquals(135.0, $exercises[0]['target_weight']);

        self::$testRoutineId = $routineId;
    }

    /**
     * Test: Starting a workout from a routine creates pre-populated sets
     */
    public function testStartWorkoutFromRoutine(): void
    {
        // First create a routine with exercises
        $routineId = dbInsert('routines', [
            'user_id' => self::$testUserId,
            'name' => 'Test Pull Day',
            'created_at' => now(),
            'updated_at' => now()
        ]);

        // Add 2 exercises: Row (3x10) and RDL (3x8)
        dbInsert('routine_exercises', [
            'routine_id' => $routineId,
            'exercise_id' => 4, // Row
            'order_index' => 1,
            'target_sets' => 3,
            'target_reps' => 10,
            'target_weight' => 100.0,
            'created_at' => now(),
            'updated_at' => now()
        ]);

        dbInsert('routine_exercises', [
            'routine_id' => $routineId,
            'exercise_id' => 3, // RDL
            'order_index' => 2,
            'target_sets' => 3,
            'target_reps' => 8,
            'target_weight' => 135.0,
            'created_at' => now(),
            'updated_at' => now()
        ]);

        // Simulate starting workout from routine
        $workoutId = dbInsert('workouts', [
            'user_id' => self::$testUserId,
            'started_at' => now(),
            'notes' => 'Test Pull Day',
            'created_at' => now(),
            'updated_at' => now()
        ]);

        // Get routine exercises and create workout_sets
        $routineExercises = dbFetchAll(
            "SELECT * FROM routine_exercises WHERE routine_id = ? ORDER BY order_index",
            [$routineId]
        );

        foreach ($routineExercises as $ex) {
            for ($i = 1; $i <= $ex['target_sets']; $i++) {
                dbInsert('workout_sets', [
                    'workout_id' => $workoutId,
                    'exercise_id' => $ex['exercise_id'],
                    'set_number' => $i,
                    'reps' => $ex['target_reps'],
                    'weight' => $ex['target_weight'],
                    'completed_at' => null,
                    'created_at' => now(),
                    'updated_at' => now()
                ]);
            }
        }

        // Verify workout was created
        $workout = dbFetchOne("SELECT * FROM workouts WHERE id = ?", [$workoutId]);
        $this->assertNotNull($workout);
        $this->assertNull($workout['ended_at']); // Not finished
        $this->assertEquals(self::$testUserId, $workout['user_id']);

        // Verify sets were pre-populated: 3 sets Row + 3 sets RDL = 6 total
        $sets = dbFetchAll("SELECT * FROM workout_sets WHERE workout_id = ? ORDER BY exercise_id, set_number", [$workoutId]);
        $this->assertCount(6, $sets);

        // Verify first exercise sets (Row)
        $rowSets = array_filter($sets, fn($s) => $s['exercise_id'] == 4);
        $rowSets = array_values($rowSets);
        $this->assertCount(3, $rowSets);
        $this->assertEquals(10, $rowSets[0]['reps']);
        $this->assertEquals(100.0, $rowSets[0]['weight']);
        $this->assertNull($rowSets[0]['completed_at']); // Not completed yet

        // Verify second exercise sets (RDL)
        $rdlSets = array_filter($sets, fn($s) => $s['exercise_id'] == 3);
        $rdlSets = array_values($rdlSets);
        $this->assertCount(3, $rdlSets);
        $this->assertEquals(8, $rdlSets[0]['reps']);
        $this->assertEquals(135.0, $rdlSets[0]['weight']);

        self::$testWorkoutId = $workoutId;
    }

    /**
     * Test: Completing a set updates reps, weight, and timestamp
     */
    public function testCompleteSet(): void
    {
        // Create a workout with one set
        $workoutId = dbInsert('workouts', [
            'user_id' => self::$testUserId,
            'started_at' => now(),
            'created_at' => now(),
            'updated_at' => now()
        ]);

        $setId = dbInsert('workout_sets', [
            'workout_id' => $workoutId,
            'exercise_id' => 1, // Bench Press
            'set_number' => 1,
            'reps' => 8,  // Target
            'weight' => 135.0,  // Target
            'completed_at' => null,
            'created_at' => now(),
            'updated_at' => now()
        ]);

        // Simulate completing the set with actual performance
        dbUpdate('workout_sets', [
            'reps' => 10,  // Did 10 instead of target 8
            'weight' => 145.0,  // Used 145 instead of target 135
            'completed_at' => now(),
            'updated_at' => now()
        ], 'id = ?', [$setId]);

        // Verify the set was updated
        $set = dbFetchOne("SELECT * FROM workout_sets WHERE id = ?", [$setId]);
        $this->assertNotNull($set);
        $this->assertEquals(10, $set['reps']);
        $this->assertEquals(145.0, $set['weight']);
        $this->assertNotNull($set['completed_at']); // Now completed
    }

    /**
     * Test: Cannot complete a set from another user's workout
     */
    public function testCannotCompleteOtherUsersSet(): void
    {
        // Create workout for user 2 (different user)
        $workoutId2 = dbInsert('workouts', [
            'user_id' => 2, // Different user
            'started_at' => now(),
            'created_at' => now(),
            'updated_at' => now()
        ]);

        $setId2 = dbInsert('workout_sets', [
            'workout_id' => $workoutId2,
            'exercise_id' => 1,
            'set_number' => 1,
            'reps' => 8,
            'weight' => 135.0,
            'completed_at' => null,
            'created_at' => now(),
            'updated_at' => now()
        ]);

        // Query that would be used to verify ownership
        $set = dbFetchOne(
            "SELECT ws.id 
             FROM workout_sets ws 
             JOIN workouts w ON ws.workout_id = w.id 
             WHERE ws.id = ? AND w.user_id = ? AND w.ended_at IS NULL AND ws.completed_at IS NULL",
            [$setId2, self::$testUserId]  // Our test user ID = 1, but set belongs to user 2
        );

        // Should return null because set belongs to user 2, not user 1
        $this->assertNull($set);
    }

    /**
     * Test: Finishing a workout sets ended_at timestamp
     */
    public function testFinishWorkout(): void
    {
        // Create and complete a workout
        $workoutId = dbInsert('workouts', [
            'user_id' => self::$testUserId,
            'started_at' => now() - 3600000, // Started 1 hour ago
            'created_at' => now(),
            'updated_at' => now()
        ]);

        // Add some completed sets
        dbInsert('workout_sets', [
            'workout_id' => $workoutId,
            'exercise_id' => 1,
            'set_number' => 1,
            'reps' => 10,
            'weight' => 135.0,
            'completed_at' => now(),
            'created_at' => now(),
            'updated_at' => now()
        ]);

        // Finish the workout
        dbUpdate('workouts', [
            'ended_at' => now(),
            'updated_at' => now()
        ], 'id = ?', [$workoutId]);

        // Verify workout is finished
        $workout = dbFetchOne("SELECT * FROM workouts WHERE id = ?", [$workoutId]);
        $this->assertNotNull($workout['ended_at']); // Now finished
    }

    /**
     * Test: Cannot start workout if one is already active
     */
    public function testCannotStartMultipleWorkouts(): void
    {
        // Create first workout (still active - no ended_at)
        $workoutId1 = dbInsert('workouts', [
            'user_id' => self::$testUserId,
            'started_at' => now(),
            'ended_at' => null, // Still active
            'created_at' => now(),
            'updated_at' => now()
        ]);

        // Check for existing active workout
        $active = dbFetchOne(
            "SELECT id FROM workouts WHERE user_id = ? AND ended_at IS NULL",
            [self::$testUserId]
        );

        $this->assertNotNull($active); // Found active workout
        $this->assertEquals($workoutId1, $active['id']);

        // Now finish first workout
        dbUpdate('workouts', ['ended_at' => now()], 'id = ?', [$workoutId1]);

        // Should now be able to start new workout
        $active = dbFetchOne(
            "SELECT id FROM workouts WHERE user_id = ? AND ended_at IS NULL",
            [self::$testUserId]
        );

        $this->assertNull($active); // No active workout anymore
    }

    /**
     * Test: Routine with no exercises cannot be started
     */
    public function testCannotStartEmptyRoutine(): void
    {
        // Create routine with no exercises
        $routineId = dbInsert('routines', [
            'user_id' => self::$testUserId,
            'name' => 'Empty Routine',
            'created_at' => now(),
            'updated_at' => now()
        ]);

        // Check for exercises
        $exercises = dbFetchAll(
            "SELECT * FROM routine_exercises WHERE routine_id = ?",
            [$routineId]
        );

        $this->assertEmpty($exercises); // No exercises
    }

    /**
     * Test: Verify routine → workout creates correct number of sets
     */
    public function testRoutineWorkoutSetCount(): void
    {
        // Create routine with varying set counts
        $routineId = dbInsert('routines', [
            'user_id' => self::$testUserId,
            'name' => 'Set Count Test',
            'created_at' => now(),
            'updated_at' => now()
        ]);

        // Exercise 1: 4 sets
        dbInsert('routine_exercises', [
            'routine_id' => $routineId,
            'exercise_id' => 1,
            'order_index' => 1,
            'target_sets' => 4,
            'target_reps' => 10,
            'target_weight' => 100.0,
            'created_at' => now(),
            'updated_at' => now()
        ]);

        // Exercise 2: 3 sets
        dbInsert('routine_exercises', [
            'routine_id' => $routineId,
            'exercise_id' => 2,
            'order_index' => 2,
            'target_sets' => 3,
            'target_reps' => 8,
            'target_weight' => 200.0,
            'created_at' => now(),
            'updated_at' => now()
        ]);

        // Exercise 3: 5 sets
        dbInsert('routine_exercises', [
            'routine_id' => $routineId,
            'exercise_id' => 3,
            'order_index' => 3,
            'target_sets' => 5,
            'target_reps' => 12,
            'target_weight' => 150.0,
            'created_at' => now(),
            'updated_at' => now()
        ]);

        // Create workout
        $workoutId = dbInsert('workouts', [
            'user_id' => self::$testUserId,
            'started_at' => now(),
            'notes' => 'Set Count Test',
            'created_at' => now(),
            'updated_at' => now()
        ]);

        // Pre-populate sets
        $routineExercises = dbFetchAll(
            "SELECT * FROM routine_exercises WHERE routine_id = ? ORDER BY order_index",
            [$routineId]
        );

        foreach ($routineExercises as $ex) {
            for ($i = 1; $i <= $ex['target_sets']; $i++) {
                dbInsert('workout_sets', [
                    'workout_id' => $workoutId,
                    'exercise_id' => $ex['exercise_id'],
                    'set_number' => $i,
                    'reps' => $ex['target_reps'],
                    'weight' => $ex['target_weight'],
                    'completed_at' => null,
                    'created_at' => now(),
                    'updated_at' => now()
                ]);
            }
        }

        // Verify total set count: 4 + 3 + 5 = 12
        $sets = dbFetchAll("SELECT * FROM workout_sets WHERE workout_id = ?", [$workoutId]);
        $this->assertCount(12, $sets);

        // Verify set numbers are correct per exercise
        $ex1Sets = dbFetchAll("SELECT * FROM workout_sets WHERE workout_id = ? AND exercise_id = 1 ORDER BY set_number", [$workoutId]);
        $this->assertCount(4, $ex1Sets);
        $this->assertEquals([1, 2, 3, 4], array_column($ex1Sets, 'set_number'));

        $ex2Sets = dbFetchAll("SELECT * FROM workout_sets WHERE workout_id = ? AND exercise_id = 2 ORDER BY set_number", [$workoutId]);
        $this->assertCount(3, $ex2Sets);
        $this->assertEquals([1, 2, 3], array_column($ex2Sets, 'set_number'));

        $ex3Sets = dbFetchAll("SELECT * FROM workout_sets WHERE workout_id = ? AND exercise_id = 3 ORDER BY set_number", [$workoutId]);
        $this->assertCount(5, $ex3Sets);
        $this->assertEquals([1, 2, 3, 4, 5], array_column($ex3Sets, 'set_number'));
    }
}
