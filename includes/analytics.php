<?php
/**
 * Analytics & Statistics Helper Functions
 * 
 * Provides advanced analytics for workout tracking including:
 * - Monthly volume trends
 * - Streak calculations
 * - 1RM estimates
 * - Exercise progression tracking
 */

/**
 * Get monthly workout statistics
 * 
 * @param int $userId User ID
 * @param int $months Number of months to look back
 * @return array Monthly stats with volume, workouts, sets
 */
function getMonthlyStats(int $userId, int $months = 6): array
{
    $since = strtotime("-$months months") * 1000;
    
    $sql = "
        SELECT 
            DATE_FORMAT(FROM_UNIXTIME(w.started_at/1000), '%Y-%m') as month,
            DATE_FORMAT(FROM_UNIXTIME(w.started_at/1000), '%b %Y') as month_label,
            COUNT(DISTINCT w.id) as workouts,
            SUM(ws.weight * ws.reps) as volume,
            COUNT(ws.id) as sets
        FROM workouts w
        LEFT JOIN workout_sets ws ON w.id = ws.workout_id AND ws.completed_at IS NOT NULL
        WHERE w.user_id = ? 
          AND w.ended_at IS NOT NULL
          AND w.started_at >= ?
        GROUP BY month, month_label
        ORDER BY month ASC
    ";
    
    return dbFetchAll($sql, [$userId, $since]);
}

/**
 * Get current workout streak
 * 
 * @param int $userId User ID
 * @return array Streak info with current count, longest, and last workout date
 */
function getWorkoutStreak(int $userId): array
{
    $workouts = dbFetchAll(
        "SELECT DATE(FROM_UNIXTIME(started_at/1000)) as workout_date 
         FROM workouts 
         WHERE user_id = ? AND ended_at IS NOT NULL 
         ORDER BY started_at DESC",
        [$userId]
    );
    
    if (empty($workouts)) {
        return ['current' => 0, 'longest' => 0, 'last_workout' => null];
    }
    
    $dates = array_unique(array_map(fn($w) => $w['workout_date'], $workouts));
    
    $currentStreak = 0;
    $longestStreak = 0;
    $currentCount = 0;
    $today = date('Y-m-d');
    $yesterday = date('Y-m-d', strtotime('-1 day'));
    
    // Check if worked out today or yesterday to continue streak
    $lastWorkout = $dates[0];
    $streakActive = ($lastWorkout === $today || $lastWorkout === $yesterday);
    
    $prevDate = null;
    foreach ($dates as $date) {
        if ($prevDate === null) {
            $currentCount = 1;
        } else {
            $diff = (new DateTime($prevDate))->diff(new DateTime($date))->days;
            if ($diff === 1) {
                $currentCount++;
            } else {
                $longestStreak = max($longestStreak, $currentCount);
                $currentCount = 1;
            }
        }
        $prevDate = $date;
    }
    
    $longestStreak = max($longestStreak, $currentCount);
    
    // Calculate current streak only if streak is active
    if ($streakActive) {
        $currentStreak = 0;
        $prevDate = null;
        foreach ($dates as $date) {
            if ($prevDate === null) {
                // First workout in list
                if ($date === $today || $date === $yesterday) {
                    $currentStreak = 1;
                } else {
                    break;
                }
            } else {
                $diff = (new DateTime($date))->diff(new DateTime($prevDate))->days;
                if ($diff === 1) {
                    $currentStreak++;
                } else {
                    break;
                }
            }
            $prevDate = $date;
        }
    }
    
    return [
        'current' => $currentStreak,
        'longest' => $longestStreak,
        'last_workout' => $lastWorkout,
        'active' => $streakActive
    ];
}

/**
 * Calculate estimated 1RM using Epley formula
 * 
 * @param float $weight Weight lifted
 * @param int $reps Reps performed
 * @return float|null Estimated 1RM or null if reps > 12
 */
function calculateOneRM(float $weight, int $reps): ?float
{
    // Epley formula: weight * (1 + reps/30)
    // Only reliable for 1-12 reps
    if ($reps < 1 || $reps > 12 || $weight <= 0) {
        return null;
    }
    
    return round($weight * (1 + $reps / 30), 1);
}

/**
 * Get personal records for each exercise
 * 
 * @param int $userId User ID
 * @return array Exercise PRs with 1RM estimates
 */
function getPersonalRecords(int $userId): array
{
    $sql = "
        SELECT 
            e.name,
            e.id as exercise_id,
            MAX(ws.weight) as max_weight,
            ws2.reps as max_weight_reps,
            MAX(ws.weight * ws.reps) as max_volume_set
        FROM workout_sets ws
        JOIN exercises e ON ws.exercise_id = e.id
        JOIN workouts w ON ws.workout_id = w.id
        LEFT JOIN workout_sets ws2 ON ws2.id = (
            SELECT id FROM workout_sets 
            WHERE exercise_id = e.id 
              AND weight = (SELECT MAX(weight) FROM workout_sets WHERE exercise_id = e.id)
            LIMIT 1
        )
        WHERE w.user_id = ? 
          AND w.ended_at IS NOT NULL
          AND ws.completed_at IS NOT NULL
        GROUP BY e.id, e.name, ws2.reps
        ORDER BY max_weight DESC
    ";
    
    $records = dbFetchAll($sql, [$userId]);
    
    foreach ($records as &$record) {
        $record['estimated_1rm'] = calculateOneRM(
            (float)$record['max_weight'], 
            (int)($record['max_weight_reps'] ?? 1)
        );
    }
    
    return $records;
}

/**
 * Get exercise progression over time
 * 
 * @param int $userId User ID
 * @param int $exerciseId Exercise ID
 * @param int $limit Number of data points
 * @return array Weight progression data
 */
function getExerciseProgression(int $userId, int $exerciseId, int $limit = 10): array
{
    $sql = "
        SELECT 
            DATE(FROM_UNIXTIME(w.started_at/1000)) as date,
            AVG(ws.weight) as avg_weight,
            MAX(ws.weight) as max_weight,
            SUM(ws.reps) as total_reps,
            COUNT(ws.id) as sets
        FROM workout_sets ws
        JOIN workouts w ON ws.workout_id = w.id
        WHERE w.user_id = ? 
          AND ws.exercise_id = ?
          AND w.ended_at IS NOT NULL
          AND ws.completed_at IS NOT NULL
        GROUP BY DATE(FROM_UNIXTIME(w.started_at/1000))
        ORDER BY w.started_at DESC
        LIMIT ?
    ";
    
    return dbFetchAll($sql, [$userId, $exerciseId, $limit]);
}

/**
 * Get body composition history
 * 
 * @param int $userId User ID
 * @return array Body scan history
 */
function getBodyCompositionHistory(int $userId): array
{
    return dbFetchAll(
        "SELECT 
            scan_date,
            total_body_weight_lbs as weight,
            percent_body_fat as body_fat,
            skeletal_muscle_mass_lbs as muscle_mass,
            bmi
         FROM body_composition_scans 
         WHERE user_id = ? 
         ORDER BY scan_date ASC",
        [$userId]
    );
}

/**
 * Get workout templates (frequently used workout names)
 * 
 * @param int $userId User ID
 * @return array Common workout names
 */
function getWorkoutTemplates(int $userId): array
{
    return dbFetchAll(
        "SELECT 
            COALESCE(NULLIF(notes, ''), 'Freestyle') as template_name,
            COUNT(*) as usage_count,
            MAX(started_at) as last_used
         FROM workouts 
         WHERE user_id = ? AND ended_at IS NOT NULL
         GROUP BY COALESCE(NULLIF(notes, ''), 'Freestyle')
         HAVING usage_count >= 2
         ORDER BY usage_count DESC, last_used DESC
         LIMIT 10",
        [$userId]
    );
}

/**
 * Export all workout data for a user
 * 
 * @param int $userId User ID
 * @return array Complete workout history with sets
 */
function exportWorkoutData(int $userId): array
{
    $workouts = dbFetchAll(
        "SELECT 
            w.id,
            DATE(FROM_UNIXTIME(w.started_at/1000)) as date,
            TIME(FROM_UNIXTIME(w.started_at/1000)) as start_time,
            TIME(FROM_UNIXTIME(w.ended_at/1000)) as end_time,
            ROUND((w.ended_at - w.started_at) / 60000) as duration_minutes,
            COALESCE(NULLIF(w.notes, ''), 'Freestyle') as workout_name
         FROM workouts w
         WHERE w.user_id = ? AND w.ended_at IS NOT NULL
         ORDER BY w.started_at DESC",
        [$userId]
    );
    
    foreach ($workouts as &$workout) {
        $workout['sets'] = dbFetchAll(
            "SELECT 
                e.name as exercise,
                e.category,
                ws.set_number,
                ws.reps,
                ws.weight,
                ws.weight * ws.reps as volume
             FROM workout_sets ws
             JOIN exercises e ON ws.exercise_id = e.id
             WHERE ws.workout_id = ? AND ws.completed_at IS NOT NULL
             ORDER BY ws.id",
            [$workout['id']]
        );
        
        $workout['total_volume'] = array_sum(array_column($workout['sets'], 'volume'));
        $workout['total_sets'] = count($workout['sets']);
        $workout['exercises'] = array_unique(array_column($workout['sets'], 'exercise'));
    }
    
    return $workouts;
}

/**
 * Format data as CSV
 * 
 * @param array $data Array of arrays
 * @return string CSV content
 */
function formatAsCSV(array $data): string
{
    if (empty($data)) {
        return '';
    }
    
    $output = fopen('php://temp', 'r+');
    
    // Headers
    fputcsv($output, array_keys($data[0]));
    
    // Data
    foreach ($data as $row) {
        fputcsv($output, $row);
    }
    
    rewind($output);
    $csv = stream_get_contents($output);
    fclose($output);
    
    return $csv;
}

/**
 * Get weekly workout consistency (last 12 weeks)
 * 
 * @param int $userId User ID
 * @return array Weekly consistency data
 */
function getWeeklyConsistency(int $userId): array
{
    $weeks = [];
    $now = time();
    
    for ($i = 11; $i >= 0; $i--) {
        $weekStart = strtotime("monday this week -$i weeks", $now) * 1000;
        $weekEnd = strtotime("monday next week -$i weeks", $now) * 1000;
        
        $count = dbFetchOne(
            "SELECT COUNT(*) as count FROM workouts 
             WHERE user_id = ? AND ended_at IS NOT NULL 
             AND started_at >= ? AND started_at < ?",
            [$userId, $weekStart, $weekEnd]
        )['count'] ?? 0;
        
        $weeks[] = [
            'week' => date('M d', $weekStart / 1000),
            'workouts' => (int)$count,
            'target_met' => $count >= 3 // Assuming 3 workouts/week target
        ];
    }
    
    return $weeks;
}
