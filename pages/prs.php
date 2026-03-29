<?php
/**
 * Personal Records Page
 * 
 * Displays heaviest lifts and best volume sets for each exercise.
 */

$userId = currentUserId();

// Get heaviest weight PRs (with the rep count for that max weight)
$weightPRs = dbFetchAll("
    SELECT 
        e.name,
        MAX(ws.weight) as max_weight,
        (SELECT reps FROM workout_sets ws2 
         JOIN workouts w2 ON ws2.workout_id = w2.id 
         WHERE ws2.exercise_id = e.id AND ws2.weight = MAX(ws.weight) 
         AND w2.user_id = ? AND w2.ended_at IS NOT NULL AND ws2.completed_at IS NOT NULL
         LIMIT 1) as reps
    FROM workout_sets ws
    JOIN exercises e ON ws.exercise_id = e.id
    JOIN workouts w ON ws.workout_id = w.id
    WHERE w.user_id = ? 
        AND w.ended_at IS NOT NULL
        AND ws.weight > 0
        AND ws.completed_at IS NOT NULL
    GROUP BY e.id, e.name
    ORDER BY max_weight DESC
    LIMIT 20
", [$userId, $userId]);

// Get best volume sets (weight × reps)
$volumePRs = dbFetchAll("
    SELECT 
        e.name, 
        ws.weight, 
        ws.reps, 
        (ws.weight * ws.reps) as volume
    FROM workout_sets ws
    JOIN exercises e ON ws.exercise_id = e.id
    JOIN workouts w ON ws.workout_id = w.id
    WHERE w.user_id = ? 
        AND w.ended_at IS NOT NULL
        AND ws.weight > 0
        AND ws.completed_at IS NOT NULL
    ORDER BY volume DESC
    LIMIT 15
", [$userId]);

renderPage('PRs', function() use ($weightPRs, $volumePRs) {
?>
<h1>Personal Records</h1>

<?php if (empty($weightPRs)): ?>
    <div class="empty">
        <div class="empty-icon">🏆</div>
        <p>No PRs yet - complete some workouts!</p>
    </div>
<?php else: ?>
    <!-- Heaviest Weight Section -->
    <div class="stats-section-title">Heaviest Weight</div>
    <div class="exercise-stat-list">
        <?php foreach ($weightPRs as $pr): ?>
            <div class="exercise-stat-item">
                <div>
                    <div class="exercise-stat-name"><?= e($pr['name']) ?></div>
                    <?php if ($pr['reps']): ?>
                        <div style="font-size: 11px; color: var(--text-dim); margin-top: 2px;">
                            <?= $pr['reps'] ?> reps
                        </div>
                    <?php endif; ?>
                </div>
                <div class="exercise-stat-value"><?= number_format($pr['max_weight'], 1) ?> <span style="font-size: 12px;">lbs</span></div>
            </div>
        <?php endforeach; ?>
    </div>
    
    <!-- Best Volume Sets Section -->
    <?php if (!empty($volumePRs)): ?>
        <div class="stats-section-title" style="margin-top: 32px;">Best Volume Sets</div>
        <div class="exercise-stat-list">
            <?php foreach ($volumePRs as $pr): ?>
                <div class="exercise-stat-item">
                    <div>
                        <div class="exercise-stat-name"><?= e($pr['name']) ?></div>
                        <div style="font-size: 11px; color: var(--text-dim); margin-top: 2px;">
                            <?= number_format($pr['weight'], 1) ?> lbs × <?= $pr['reps'] ?> reps
                        </div>
                    </div>
                    <div class="exercise-stat-value"><?= number_format($pr['volume']) ?> <span style="font-size: 12px;">lbs</span></div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
<?php endif; ?>
<?php
});
