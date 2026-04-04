<?php
/**
 * Progression Rules Configuration
 * 
 * Single source of truth for exercise progression recommendations.
 * Defines how much weight to add when an exercise becomes "easy".
 * 
 * @package WorkoutTracker\Progression
 * 
 * Progression Rules Structure:
 * - increment: Base weight increase (lbs)
 * - threshold: Optional weight threshold where increment changes
 * - after_threshold: New increment after threshold is reached
 * - rep_increment: How to increase reps when hitting target (optional)
 * - note: Human-readable display string
 */

/**
 * Get all progression rules
 * 
 * Covers all exercises in the 3-day functional fitness program:
 * - Day A: Hack Squat, Bench Press, RDL, Seated Row, Leg Curl, Crunch
 * - Day B: Barbell Row, RDL, Lat Pulldown, Face Pull, Bicep Curl, Low Back - Roc It
 * - Day C: Tire Squats, Chest Press, Leg Extension, Seated Dip, Shrug, Sled Push
 * 
 * @return array Exercise name => progression rule array
 */
function getProgressionRules(): array
{
    return [
        /**
         * DAY A - Lower + Bench
         */
        'Hack Squat' => [
            'increment' => 15,
            'threshold' => 200,
            'after_threshold' => 20,
            'note' => '+15 lbs (then +20 after 200)'
        ],
        'Bench Press' => [
            'increment' => 10,
            'threshold' => 150,
            'after_threshold' => 15,
            'note' => '+10 lbs (then +15 after 150)'
        ],
        'Romanian Deadlift' => [
            'increment' => 15,
            'threshold' => 135,
            'after_threshold' => 20,
            'note' => '+15 lbs (then +20 after 135)'
        ],
        'Diverging Seated Row' => [
            'increment' => 10,
            'threshold' => null,
            'after_threshold' => null,
            'note' => '+10 lbs'
        ],
        'Leg Curl' => [
            'increment' => 10,
            'threshold' => 100,
            'after_threshold' => 15,
            'note' => '+10 lbs (then +15 after 100)'
        ],
        'Crunch' => [
            'increment' => 5,
            'threshold' => null,
            'after_threshold' => null,
            'note' => '+5 lbs or +5 reps'
        ],
        
        /**
         * DAY B - Upper Pull + RDL
         */
        'Barbell Row' => [
            'increment' => 10,
            'threshold' => 115,
            'after_threshold' => 15,
            'note' => '+10 lbs (then +15 after 115)'
        ],
        'Lat Pulldown' => [
            'increment' => 10,
            'threshold' => 130,
            'after_threshold' => 15,
            'note' => '+10 lbs (then +15 after 130)'
        ],
        'Face Pull' => [
            'increment' => 5,
            'threshold' => null,
            'after_threshold' => null,
            'note' => '+5 lbs or +5 reps'
        ],
        'Bicep Curl' => [
            'increment' => 5,
            'threshold' => 50,
            'after_threshold' => 10,
            'note' => '+5 lbs (then +10 after 50)'
        ],
        'Low Back - Roc It' => [
            'increment' => 15,
            'threshold' => 100,
            'after_threshold' => 20,
            'note' => '+15 lbs (then +20 after 100)'
        ],
        
        /**
         * DAY C - Tire + Conditioning
         */
        'Tire Squats' => [
            'increment' => 20,
            'threshold' => 400,
            'after_threshold' => 30,
            'note' => '+20 lbs (then +30 after 400)'
        ],
        'Chest Press' => [
            'increment' => 10,
            'threshold' => 150,
            'after_threshold' => 15,
            'note' => '+10 lbs (then +15 after 150)'
        ],
        'Leg Extension' => [
            'increment' => 10,
            'threshold' => 130,
            'after_threshold' => 15,
            'note' => '+10 lbs (then +15 after 130)'
        ],
        'Seated Dip' => [
            'increment' => 10,
            'threshold' => 130,
            'after_threshold' => 15,
            'note' => '+10 lbs (then +15 after 130)'
        ],
        'Shrug' => [
            'increment' => 10,
            'threshold' => 130,
            'after_threshold' => 15,
            'note' => '+10 lbs (then +15 after 130)'
        ],
        'Sled Push' => [
            'increment' => 10,
            'threshold' => null,
            'after_threshold' => null,
            'note' => '+10 lbs or +2 reps'
        ],
        
        /**
         * LEGACY - Keeping existing rules for backwards compatibility
         */
        'Back Extension' => [
            'increment' => 15,
            'threshold' => null,
            'after_threshold' => null,
            'note' => '+15 lbs'
        ],
        'Leg Press' => [
            'increment' => 15,
            'threshold' => null,
            'after_threshold' => null,
            'note' => '+15 lbs'
        ],
        'Converging Chest Press' => [
            'increment' => 15,
            'threshold' => null,
            'after_threshold' => null,
            'note' => '+15 lbs'
        ],
        'Tricep Extensions' => [
            'increment' => 10,
            'threshold' => null,
            'after_threshold' => null,
            'note' => '+10 lbs'
        ],
        'Shoulder Press - Machine' => [
            'increment' => 20,
            'threshold' => null,
            'after_threshold' => null,
            'note' => '+20 lbs'
        ],
    ];
}

/**
 * Get progression rule for a specific exercise
 * 
 * @param string $exerciseName
 * @return array|null
 */
function getProgressionRule(string $exerciseName): ?array
{
    $rules = getProgressionRules();
    return $rules[$exerciseName] ?? null;
}

/**
 * Calculate suggested next weight for progression
 * 
 * @param string $exerciseName
 * @param float|null $lastWeight
 * @return float|null
 */
function suggestNextWeight(string $exerciseName, ?float $lastWeight): ?float
{
    if ($lastWeight === null) {
        return null;
    }
    
    $rule = getProgressionRule($exerciseName);
    
    if ($rule === null) {
        return $lastWeight;
    }
    
    $increment = $rule['increment'];
    
    if ($rule['threshold'] !== null && $lastWeight >= $rule['threshold']) {
        $increment = $rule['after_threshold'] ?? $increment;
    }
    
    return $lastWeight + $increment;
}

/**
 * Get progression note for display
 * 
 * @param string $exerciseName
 * @return string
 */
function progressionNote(string $exerciseName): string
{
    $rule = getProgressionRule($exerciseName);
    return $rule['note'] ?? '';
}

/**
 * Get progression rules as JSON for JavaScript
 * 
 * @return string JSON encoded rules
 */
function getProgressionRulesJson(): string
{
    $rules = getProgressionRules();
    $simplified = [];
    
    foreach ($rules as $name => $rule) {
        $simplified[$name] = [
            'inc' => $rule['increment'],
            'special' => $rule['threshold'] !== null ? 'threshold' : null,
            'threshold' => $rule['threshold'],
            'after_threshold' => $rule['after_threshold']
        ];
    }
    
    return json_encode($simplified);
}

/**
 * Analyze routine performance for a specific exercise
 * 
 * Checks the last N completed workouts that contained this routine exercise
 * and calculates performance metrics.
 * 
 * @param int $userId User ID
 * @param int $routineExerciseId The routine_exercises ID to analyze
 * @param int $lookbackWorkouts How many recent workouts to check (default: 2)
 * @return array Performance stats including:
 *   - sessions_analyzed: Number of workouts found
 *   - total_sets: Total sets performed
 *   - sets_hit_target: Sets where actual reps >= target reps
 *   - avg_reps: Average reps performed
 *   - avg_weight: Average weight used
 *   - max_weight: Maximum weight used
 *   - performance_pct: Percentage of sets meeting target
 *   - should_progress: Boolean - true if ready for weight increase
 */
function analyzeRoutineExercisePerformance(int $userId, int $routineExerciseId, int $lookbackWorkouts = 2): array
{
    // Get routine exercise details (target sets/reps/weight)
    $routineExercise = dbFetchOne(
        "SELECT re.*, e.name as exercise_name 
         FROM routine_exercises re 
         JOIN exercises e ON re.exercise_id = e.id 
         WHERE re.id = ?",
        [$routineExerciseId]
    );
    
    if (!$routineExercise) {
        return [
            'error' => 'Routine exercise not found',
            'should_progress' => false
        ];
    }
    
    // Get recent workouts for this user (completed ones with this exercise)
    // We need to find workouts that used exercises from this routine
    $recentWorkouts = dbFetchAll(
        "SELECT DISTINCT w.id, w.started_at, w.ended_at, w.notes
         FROM workouts w
         JOIN workout_sets ws ON w.id = ws.workout_id
         WHERE w.user_id = ? 
           AND w.ended_at IS NOT NULL
           AND ws.exercise_id = ?
         ORDER BY w.ended_at DESC
         LIMIT ?",
        [$userId, $routineExercise['exercise_id'], $lookbackWorkouts]
    );
    
    if (empty($recentWorkouts)) {
        return [
            'exercise_name' => $routineExercise['exercise_name'],
            'target_reps' => $routineExercise['target_reps'],
            'target_weight' => $routineExercise['target_weight'],
            'sessions_analyzed' => 0,
            'total_sets' => 0,
            'sets_hit_target' => 0,
            'avg_reps' => 0,
            'avg_weight' => 0,
            'max_weight' => 0,
            'performance_pct' => 0,
            'should_progress' => false,
            'reason' => 'No recent workout data'
        ];
    }
    
    // Get all sets from these workouts for this exercise
    $workoutIds = array_column($recentWorkouts, 'id');
    $placeholders = implode(',', array_fill(0, count($workoutIds), '?'));
    
    $sets = dbFetchAll(
        "SELECT reps, weight, completed_at 
         FROM workout_sets 
         WHERE workout_id IN ({$placeholders}) 
           AND exercise_id = ?
           AND completed_at IS NOT NULL",
        array_merge($workoutIds, [$routineExercise['exercise_id']])
    );
    
    if (empty($sets)) {
        return [
            'exercise_name' => $routineExercise['exercise_name'],
            'sessions_analyzed' => count($recentWorkouts),
            'total_sets' => 0,
            'should_progress' => false,
            'reason' => 'No completed sets found'
        ];
    }
    
    // Calculate stats
    $totalSets = count($sets);
    $setsHitTarget = 0;
    $totalReps = 0;
    $totalWeight = 0;
    $maxWeight = 0;
    
    foreach ($sets as $set) {
        $totalReps += (int)$set['reps'];
        $totalWeight += (float)$set['weight'];
        $maxWeight = max($maxWeight, (float)$set['weight']);
        
        // Set counts as "hitting target" if reps >= target reps
        if ((int)$set['reps'] >= $routineExercise['target_reps']) {
            $setsHitTarget++;
        }
    }
    
    $avgReps = $totalReps / $totalSets;
    $avgWeight = $totalWeight / $totalSets;
    $performancePct = ($setsHitTarget / $totalSets) * 100;
    
    // Suggest progression if:
    // - At least 2 sessions analyzed
    // - Performance is 85%+ (hitting target reps most of the time)
    // - This means the weight is becoming too easy
    $shouldProgress = (
        count($recentWorkouts) >= $lookbackWorkouts && 
        $performancePct >= 85
    );
    
    return [
        'exercise_name' => $routineExercise['exercise_name'],
        'routine_exercise_id' => $routineExerciseId,
        'target_reps' => $routineExercise['target_reps'],
        'target_weight' => $routineExercise['target_weight'],
        'sessions_analyzed' => count($recentWorkouts),
        'total_sets' => $totalSets,
        'sets_hit_target' => $setsHitTarget,
        'avg_reps' => round($avgReps, 1),
        'avg_weight' => round($avgWeight, 1),
        'max_weight' => round($maxWeight, 1),
        'performance_pct' => round($performancePct, 1),
        'should_progress' => $shouldProgress,
        'reason' => $shouldProgress 
            ? "Hit target reps {$performancePct}% across {$totalSets} sets" 
            : "Performance at {$performancePct}% (need 85%+ to progress)"
    ];
}

/**
 * Get progression suggestions for all exercises in a routine
 * 
 * Analyzes each exercise in a routine and returns suggestions for which
 * ones are ready for a weight increase.
 * 
 * @param int $userId User ID
 * @param int $routineId Routine to analyze
 * @return array Array of suggestions per exercise
 */
function getRoutineProgressionSuggestions(int $userId, int $routineId): array
{
    // Verify ownership
    $routine = dbFetchOne(
        "SELECT * FROM routines WHERE id = ? AND user_id = ?",
        [$routineId, $userId]
    );
    
    if (!$routine) {
        return ['error' => 'Routine not found'];
    }
    
    // Get all exercises in this routine
    $routineExercises = dbFetchAll(
        "SELECT re.id, re.exercise_id, re.target_sets, re.target_reps, re.target_weight, e.name
         FROM routine_exercises re
         JOIN exercises e ON re.exercise_id = e.id
         WHERE re.routine_id = ?
         ORDER BY re.order_index",
        [$routineId]
    );
    
    $suggestions = [];
    
    foreach ($routineExercises as $re) {
        $analysis = analyzeRoutineExercisePerformance($userId, $re['id'], 2);
        
        $suggestion = [
            'routine_exercise_id' => $re['id'],
            'exercise_name' => $re['name'],
            'current_target_weight' => $re['target_weight'],
            'current_target_reps' => $re['target_reps'],
            'performance' => $analysis
        ];
        
        // Add suggested new weight if ready to progress
        if ($analysis['should_progress']) {
            $suggestedWeight = suggestNextWeight(
                $re['name'], 
                $re['target_weight']
            );
            
            if ($suggestedWeight !== null && $suggestedWeight > $re['target_weight']) {
                $suggestion['suggested_weight'] = $suggestedWeight;
                $suggestion['weight_increase'] = $suggestedWeight - $re['target_weight'];
            }
        }
        
        $suggestions[] = $suggestion;
    }
    
    return $suggestions;
}

/**
 * Get the number of exercises ready for progression in a routine
 * 
 * @param int $userId
 * @param int $routineId
 * @return int Count of exercises ready for weight increase
 */
function countRoutineExercisesReadyForProgression(int $userId, int $routineId): int
{
    $suggestions = getRoutineProgressionSuggestions($userId, $routineId);
    
    if (isset($suggestions['error'])) {
        return 0;
    }
    
    $count = 0;
    foreach ($suggestions as $s) {
        if (isset($s['suggested_weight'])) {
            $count++;
        }
    }
    
    return $count;
}
