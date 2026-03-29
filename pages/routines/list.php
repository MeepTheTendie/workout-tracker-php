<?php
/**
 * Routines List Page
 * 
 * Displays user's workout routines with exercise summaries
 * and quick-start actions.
 */

$userId = currentUserId();

// Fetch all routines with exercise counts
$routines = dbFetchAll(
    "SELECT r.*, COUNT(re.id) as exercise_count 
     FROM routines r 
     LEFT JOIN routine_exercises re ON r.id = re.routine_id 
     WHERE r.user_id = ? 
     GROUP BY r.id 
     ORDER BY r.created_at DESC",
    [$userId]
);

renderPage('Routines', function() use ($routines) {
    ?>
    <h1>Routines</h1>
    
    <div class="routines-header">
        <a href="/routines/create" class="btn btn-primary">+ NEW ROUTINE</a>
    </div>
    
    <?php if (empty($routines)): ?>
        <div class="empty">
            <div class="empty-icon">📋</div>
            <p>No routines yet</p>
            <p style="font-size: 12px; margin-top: 8px;">Create a routine to quickly start your favorite workouts</p>
        </div>
    <?php else: ?>
        <?php foreach ($routines as $routine): 
            // Get exercises for this routine
            $exercises = dbFetchAll(
                "SELECT e.name, re.target_sets, re.target_reps, re.target_weight 
                 FROM routine_exercises re 
                 JOIN exercises e ON re.exercise_id = e.id 
                 WHERE re.routine_id = ? 
                 ORDER BY re.order_index
                 LIMIT 5",
                [$routine['id']]
            );
            
            $totalExercises = (int)$routine['exercise_count'];
        ?>
            <div class="routine-card">
                <div class="routine-card-header">
                    <div class="routine-card-title"><?= e($routine['name']) ?></div>
                    <span class="routine-card-chevron">›</span>
                </div>
                
                <?php if ($routine['description']): ?>
                    <div class="routine-card-description"><?= e($routine['description']) ?></div>
                <?php endif; ?>
                
                <?php if (!empty($exercises)): ?>
                    <ul class="routine-card-exercises">
                        <?php foreach ($exercises as $exercise): ?>
                            <li><?= e($exercise['name']) ?></li>
                        <?php endforeach; ?>
                        <?php if ($totalExercises > 5): ?>
                            <li style="color: var(--text-dim); font-style: italic;">
                                + <?= $totalExercises - 5 ?> more
                            </li>
                        <?php endif; ?>
                    </ul>
                <?php else: ?>
                    <div class="routine-card-description" style="font-style: italic;">
                        No exercises added yet
                    </div>
                <?php endif; ?>
                
                <div class="routine-card-actions">
                    <form method="POST" action="/action/routines/start" style="flex: 1;">
                        <?= csrfField() ?>
                        <input type="hidden" name="routine_id" value="<?= $routine['id'] ?>">
                        <button type="submit" class="btn btn-primary">START</button>
                    </form>
                    
                    <a href="/routines/edit?id=<?= $routine['id'] ?>" class="btn btn-small btn-secondary">EDIT</a>
                    
                    <form method="POST" action="/action/routines/delete" style="width: auto;" 
                          onsubmit="return confirm('Delete &quot;<?= e($routine['name']) ?>&quot;? This cannot be undone.')">
                        <?= csrfField() ?>
                        <input type="hidden" name="routine_id" value="<?= $routine['id'] ?>">
                        <button type="submit" class="btn btn-icon" title="Delete routine">×</button>
                    </form>
                </div>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>
    <?php
});
