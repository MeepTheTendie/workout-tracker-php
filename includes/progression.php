<?php
/**
 * Shared Progression Rules
 * Used across routines, workout logging, and stats pages
 */

// Progression rules by exercise name
$GLOBALS['PROGRESSION_RULES'] = [
    'Back Extension' => ['increment' => 15, 'unit' => 'lbs', 'special' => null],
    'Low Back - Roc It' => ['increment' => 15, 'unit' => 'lbs', 'special' => 'roc_it'], // +20 after 45
    'Diverging Seated Row' => ['increment' => 10, 'unit' => 'lbs', 'special' => null],
    'Leg Press' => ['increment' => 15, 'unit' => 'lbs', 'special' => null],
    'Converging Chest Press' => ['increment' => 15, 'unit' => 'lbs', 'special' => null],
    'Tricep Extensions' => ['increment' => 10, 'unit' => 'lbs', 'special' => null],
    'Bicep Curl' => ['increment' => 15, 'unit' => 'lbs', 'special' => null],
    'Shoulder Press - Machine' => ['increment' => 20, 'unit' => 'lbs', 'special' => null],
];

/**
 * Get the next weight for an exercise based on last weight and progression rules
 * 
 * @param string $exerciseName Name of the exercise
 * @param float $lastWeight Last weight used
 * @return float|null Next recommended weight or null if no rule exists
 */
function getNextWeight(string $exerciseName, ?float $lastWeight): ?float
{
    $rules = $GLOBALS['PROGRESSION_RULES'];
    
    if (!isset($rules[$exerciseName]) || $lastWeight === null) {
        return $lastWeight;
    }
    
    $rule = $rules[$exerciseName];
    $increment = $rule['increment'];
    
    // Special case: Low Back Roc It switches to +20 after 45 lbs
    if ($rule['special'] === 'roc_it' && $lastWeight >= 45) {
        $increment = 20;
    }
    
    return $lastWeight + $increment;
}

/**
 * Get progression note for display
 * 
 * @param string $exerciseName Name of the exercise
 * @param float $lastWeight Last weight used
 * @return string Note like "+15 lbs" or "+20 lbs (after 45)"
 */
function getProgressionNote(string $exerciseName, ?float $lastWeight): string
{
    $rules = $GLOBALS['PROGRESSION_RULES'];
    
    if (!isset($rules[$exerciseName]) || $lastWeight === null) {
        return '';
    }
    
    $rule = $rules[$exerciseName];
    $increment = $rule['increment'];
    $note = '+' . $increment . ' ' . $rule['unit'];
    
    // Special case note
    if ($rule['special'] === 'roc_it') {
        if ($lastWeight >= 45) {
            $note = '+20 lbs';
        } else {
            $note .= ' (then +20 after 45)';
        }
    }
    
    return $note;
}

/**
 * Get last weight for each exercise for a user
 * 
 * @param PDO $db Database connection
 * @param int $userId User ID
 * @return array Associative array [exercise_name => weight]
 */
function getLastWeights(PDO $db, int $userId): array
{
    $stmt = $db->prepare("
        SELECT e.name, ws.weight 
        FROM workout_sets ws 
        JOIN exercises e ON ws.exercise_id = e.id 
        JOIN workouts w ON ws.workout_id = w.id 
        WHERE w.user_id = ? 
          AND w.ended_at IS NOT NULL 
          AND ws.weight > 0
        ORDER BY ws.id DESC
    ");
    $stmt->execute([$userId]);
    
    $lastWeights = [];
    while ($row = $stmt->fetch()) {
        if (!isset($lastWeights[$row['name']])) {
            $lastWeights[$row['name']] = (float) $row['weight'];
        }
    }
    
    return $lastWeights;
}

/**
 * Get last weights with full details (weight, reps, date)
 * 
 * @param PDO $db Database connection
 * @param int $userId User ID
 * @return array Associative array [exercise_name => [weight, reps, date]]
 */
function getLastWeightsDetailed(PDO $db, int $userId): array
{
    $stmt = $db->prepare("
        SELECT e.name, ws.weight, ws.reps, w.started_at
        FROM workout_sets ws 
        JOIN exercises e ON ws.exercise_id = e.id 
        JOIN workouts w ON ws.workout_id = w.id 
        WHERE w.user_id = ? 
          AND w.ended_at IS NOT NULL 
          AND ws.weight > 0
        ORDER BY w.started_at DESC, ws.id DESC
    ");
    $stmt->execute([$userId]);
    
    $lastWeights = [];
    while ($row = $stmt->fetch()) {
        if (!isset($lastWeights[$row['name']])) {
            $lastWeights[$row['name']] = [
                'weight' => (float) $row['weight'],
                'reps' => (int) $row['reps'],
                'date' => $row['started_at']
            ];
        }
    }
    
    return $lastWeights;
}
