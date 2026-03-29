<?php
/**
 * Workout Detail View Page
 * 
 * Shows complete details of a single workout including
 * all exercises, sets, weights, and volume calculations.
 */

$userId = currentUserId();
$workoutId = intParam($_GET['id'] ?? 0);

if ($workoutId <= 0) {
    redirect('/workouts/history', 'Invalid workout', 'error');
}

$workout = dbFetchOne(
    "SELECT * FROM workouts WHERE id = ? AND user_id = ?",
    [$workoutId, $userId]
);

if (!$workout) {
    redirect('/workouts/history', 'Workout not found', 'error');
}

// Get all sets grouped by exercise
$sets = dbFetchAll(
    "SELECT ws.*, e.name as exercise_name, e.category 
     FROM workout_sets ws 
     JOIN exercises e ON ws.exercise_id = e.id 
     WHERE ws.workout_id = ? 
     ORDER BY ws.id",
    [$workoutId]
);

$exercises = [];
$totalVolume = 0;
$totalSets = 0;

foreach ($sets as $set) {
    $exName = $set['exercise_name'];
    if (!isset($exercises[$exName])) {
        $exercises[$exName] = [
            'sets' => [],
            'volume' => 0,
            'category' => $set['category']
        ];
    }
    $exercises[$exName]['sets'][] = $set;
    if ($set['completed_at']) {
        $exercises[$exName]['volume'] += ($set['weight'] * $set['reps']);
        $totalVolume += ($set['weight'] * $set['reps']);
        $totalSets++;
    }
}

$duration = 0;
if ($workout['ended_at'] && $workout['started_at']) {
    $duration = (int)(($workout['ended_at'] - $workout['started_at']) / 1000 / 60);
}

$workoutName = $workout['notes'] ?: 'Workout';

renderPage('Workout Details', function() use ($workout, $workoutName, $exercises, $totalVolume, $totalSets, $duration) {
    ?>
    <h1><?= e($workoutName) ?></h1>
    
    <!-- Workout Summary Card -->
    <div class="workout-summary-card">
        <div class="workout-summary-date"><?= formatDate((int)$workout['started_at']) ?></div>
        
        <div class="stats-grid" style="margin-top: 16px;">
            <div class="stat-box">
                <div class="stat-value"><?= $totalSets ?></div>
                <div class="stat-label">Sets</div>
            </div>
            <div class="stat-box">
                <div class="stat-value"><?= number_format($totalVolume / 1000, 1) ?>k</div>
                <div class="stat-label">Volume</div>
            </div>
            <div class="stat-box">
                <div class="stat-value"><?= $duration ?></div>
                <div class="stat-label">Minutes</div>
            </div>
            <div class="stat-box">
                <div class="stat-value"><?= count($exercises) ?></div>
                <div class="stat-label">Exercises</div>
            </div>
        </div>
    </div>
    
    <!-- Exercise Details -->
    <div class="stats-section-title" style="margin-top: 24px;">Exercises</div>
    
    <?php foreach ($exercises as $exName => $data): ?>
        <div class="exercise-detail-card">
            <div class="exercise-detail-header">
                <span class="exercise-detail-name"><?= e($exName) ?></span>
                <span class="exercise-detail-volume"><?= number_format($data['volume']) ?> lbs</span>
            </div>
            
            <div class="exercise-detail-sets">
                <?php foreach ($data['sets'] as $set): ?>
                    <div class="exercise-detail-set <?= $set['completed_at'] ? 'completed' : '' ?>">
                        <span class="set-number">Set <?= $set['set_number'] ?></span>
                        <span class="set-weight"><?= formatWeight($set['weight']) ?></span>
                        <span class="set-reps"><?= $set['reps'] ?> reps</span>
                        <?php if ($set['completed_at']): ?>
                            <span class="set-check">✓</span>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    <?php endforeach; ?>
    
    <a href="/workouts/history" class="btn" style="margin-top: 24px;">← Back to History</a>
    <?php
});
