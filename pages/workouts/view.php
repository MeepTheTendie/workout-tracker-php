<?php
/**
 * Workout Detail View Page
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
    $duration = (int)(($workout['ended_at'] - $workout['started_at']) / 1000 / 60); // minutes
}

renderPage('Workout Details', function() use ($workout, $exercises, $totalVolume, $totalSets, $duration) {
    ?>
    <h1>Workout Details</h1>
    
    <div class="card" style="margin-bottom: 24px;">
        <div class="stat-card" style="background: transparent; border: none;">
            <div class="stat-value"><?= formatDate((int)$workout['started_at']) ?></div>
        </div>
        
        <div class="stats-grid" style="margin-top: 16px;">
            <div class="stat-card">
                <div class="stat-value"><?= $totalSets ?></div>
                <div class="stat-label">Sets</div>
            </div>
            <div class="stat-card">
                <div class="stat-value"><?= number_format($totalVolume / 1000, 1) ?>k</div>
                <div class="stat-label">Lbs</div>
            </div>
            <div class="stat-card">
                <div class="stat-value"><?= $duration ?></div>
                <div class="stat-label">Minutes</div>
            </div>
            <div class="stat-card">
                <div class="stat-value"><?= count($exercises) ?></div>
                <div class="stat-label">Exercises</div>
            </div>
        </div>
        
        <?php if ($workout['notes']): ?>
            <div style="margin-top: 16px; padding-top: 16px; border-top: 1px solid var(--border); font-size: 13px; color: var(--text-dim);">
                <?= e($workout['notes']) ?>
            </div>
        <?php endif; ?>
    </div>
    
    <h2>Exercises</h2>
    
    <?php foreach ($exercises as $exName => $data): ?>
        <div class="exercise-block">
            <div class="exercise-header">
                <span class="exercise-name"><?= e($exName) ?></span>
            </div>
            
            <?php foreach ($data['sets'] as $set): ?>
                <div class="set-row">
                    <span class="set-number">Set <?= $set['set_number'] ?></span>
                    <span class="set-complete">
                        <?= $set['reps'] ?> reps @ <?= formatWeight($set['weight']) ?>
                    </span>
                </div>
            <?php endforeach; ?>
            
            <div class="volume-display">
                Total: <?= number_format($data['volume']) ?> lbs
            </div>
        </div>
    <?php endforeach; ?>
    
    <a href="/workouts/history" class="btn" style="margin-top: 24px;">← Back to History</a>
    <?php
});
