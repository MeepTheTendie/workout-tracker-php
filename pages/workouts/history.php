<?php
/**
 * Workout History Page
 * 
 * Paginated list of completed workouts with summary stats.
 */

$userId = currentUserId();
$page = intParam($_GET['page'] ?? 1, 1, 1);
$perPage = 20;
$offset = ($page - 1) * $perPage;

$workouts = dbFetchAll(
    "SELECT id, started_at, ended_at, notes 
     FROM workouts 
     WHERE user_id = ? AND ended_at IS NOT NULL 
     ORDER BY started_at DESC 
     LIMIT ? OFFSET ?",
    [$userId, $perPage, $offset]
);

$totalCount = dbFetchOne(
    "SELECT COUNT(*) as count FROM workouts WHERE user_id = ? AND ended_at IS NOT NULL",
    [$userId]
)['count'] ?? 0;

$totalPages = (int)ceil($totalCount / $perPage);

renderPage('History', function() use ($workouts, $page, $totalPages, $totalCount) {
    ?>
    <h1>History</h1>
    
    <?php if (empty($workouts)): ?>
        <div class="empty">
            <div class="empty-icon">📊</div>
            <p>No workouts yet</p>
            <a href="/workouts/log" class="btn btn-primary" style="margin-top: 16px; width: auto; display: inline-flex;">Start First Workout</a>
        </div>
    <?php else: ?>
        <div class="workout-list">
            <?php foreach ($workouts as $workout): 
                $stats = dbFetchOne(
                    "SELECT 
                        COUNT(*) as set_count,
                        SUM(weight * reps) as volume
                      FROM workout_sets 
                      WHERE workout_id = ? AND completed_at IS NOT NULL",
                    [$workout['id']]
                );
                
                $exercises = dbFetchAll(
                    "SELECT DISTINCT e.name 
                     FROM workout_sets ws 
                     JOIN exercises e ON ws.exercise_id = e.id 
                     WHERE ws.workout_id = ? AND ws.completed_at IS NOT NULL
                     LIMIT 3",
                    [$workout['id']]
                );
                
                $workoutName = $workout['notes'] ?: 'Workout';
            ?>
                <a href="/workouts/view?id=<?= $workout['id'] ?>" class="workout-list-item">
                    <div class="workout-list-content">
                        <div class="workout-list-date"><?= formatDate((int)$workout['started_at']) ?></div>
                        <div class="workout-list-name"><?= e($workoutName) ?></div>
                        <div class="workout-list-meta">
                            <?= $stats['set_count'] ?? 0 ?> sets • <?= number_format($stats['volume'] ?? 0) ?> lbs
                            <?php if (!empty($exercises)): ?>
                                <span class="workout-list-exercises">
                                    • <?= implode(', ', array_map(fn($e) => $e['name'], $exercises)) ?>
                                </span>
                            <?php endif; ?>
                        </div>
                    </div>
                    <span class="workout-list-chevron">›</span>
                </a>
            <?php endforeach; ?>
        </div>
        
        <?php if ($totalPages > 1): ?>
            <div class="pagination">
                <?php if ($page > 1): ?>
                    <a href="/workouts/history?page=<?= $page - 1 ?>" class="btn btn-small" style="width: auto;">← Prev</a>
                <?php endif; ?>
                
                <span class="pagination-info">
                    Page <?= $page ?> of <?= $totalPages ?>
                </span>
                
                <?php if ($page < $totalPages): ?>
                    <a href="/workouts/history?page=<?= $page + 1 ?>" class="btn btn-small" style="width: auto;">Next →</a>
                <?php endif; ?>
            </div>
        <?php endif; ?>
    <?php endif; ?>
    <?php
});
