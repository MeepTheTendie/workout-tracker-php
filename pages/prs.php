<?php
/**
 * PRs Page - Redesigned
 */

requireAuth();

$userId = currentUserId();

// Get heaviest weight PRs
$weightPRs = dbFetchAll("
    SELECT e.name, MAX(ws.weight) as max_weight, ws.reps
    FROM workout_sets ws
    JOIN exercises e ON ws.exercise_id = e.id
    JOIN workouts w ON ws.workout_id = w.id
    WHERE w.user_id = ? 
        AND w.ended_at IS NOT NULL
        AND ws.weight > 0
        AND ws.completed_at IS NOT NULL
    GROUP BY e.id
    ORDER BY max_weight DESC
    LIMIT 20
", [$userId]);

// Get best volume sets (weight × reps)
$volumePRs = dbFetchAll("
    SELECT e.name, ws.weight, ws.reps, (ws.weight * ws.reps) as volume
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
<h1>PERSONAL RECORDS</h1>

<?php if (empty($weightPRs)): ?>
    <div class="empty">
        <p>No data yet - complete some workouts!</p>
    </div>
<?php else: ?>
    <!-- Heaviest Weight Section -->
    <div class="stats-section-title">HEAVIEST WEIGHT</div>
    <div class="exercise-stat-list">
        <?php foreach ($weightPRs as $pr): ?>
            <div class="exercise-stat-item">
                <div class="exercise-stat-name"><?= e($pr['name']) ?></div>
                <div class="exercise-stat-value"><?= number_format($pr['max_weight'], 2) ?> lbs</div>
            </div>
        <?php endforeach; ?>
    </div>
    
    <!-- Best Volume Sets Section -->
    <?php if (!empty($volumePRs)): ?>
        <div class="stats-section-title" style="margin-top: 32px;">BEST SINGLE SETS (WEIGHT × REPS)</div>
        <div class="exercise-stat-list">
            <?php foreach ($volumePRs as $pr): ?>
                <div class="exercise-stat-item">
                    <div>
                        <div class="exercise-stat-name"><?= e($pr['name']) ?></div>
                        <div style="font-size: 11px; color: var(--text-dim); margin-top: 2px;">
                            <?= number_format($pr['weight'], 2) ?> lbs × <?= $pr['reps'] ?> reps
                        </div>
                    </div>
                    <div class="exercise-stat-value"><?= number_format($pr['volume']) ?></div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
<?php endif; ?>
<?php
});
