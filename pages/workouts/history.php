<?php
/**
 * Workout History Page
 */

$userId = currentUserId();
$page = intParam($_GET['page'] ?? 1, 1, 1);
$perPage = 20;
$offset = ($page - 1) * $perPage;

$workouts = dbFetchAll(
    "SELECT id, started_at, ended_at 
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

renderPage('Workout History', function() use ($workouts, $page, $totalPages, $totalCount) {
    ?>
    <h1>History</h1>
    
    <?php if (empty($workouts)): ?>
        <div class="empty">
            <div class="empty-icon">📊</div>
            <p>No workouts yet</p>
            <a href="/workouts/log" class="btn btn-primary" style="margin-top: 16px;">Start Your First Workout</a>
        </div>
    <?php else: ?>
        <div class="list">
            <?php foreach ($workouts as $workout): 
                $stats = dbFetchOne(
                    "SELECT 
                        COUNT(*) as set_count,
                        SUM(weight * reps) as volume
                      FROM workout_sets 
                      WHERE workout_id = ?",
                    [$workout['id']]
                );
                
                $exercises = dbFetchAll(
                    "SELECT DISTINCT e.name 
                     FROM workout_sets ws 
                     JOIN exercises e ON ws.exercise_id = e.id 
                     WHERE ws.workout_id = ?
                     LIMIT 3",
                    [$workout['id']]
                );
            ?>
                <a href="/workouts/view?id=<?= $workout['id'] ?>" class="list-item" style="text-decoration: none; color: inherit;">
                    <div>
                        <div class="list-item-name"><?= formatDate((int)$workout['started_at']) ?></div>
                        <div class="list-item-meta">
                            <?= $stats['set_count'] ?? 0 ?> sets • 
                            <?= number_format($stats['volume'] ?? 0) ?> lbs
                            <?php if (!empty($exercises)): ?>
                                • <?= implode(', ', array_map(fn($e) => $e['name'], $exercises)) ?>
                            <?php endif; ?>
                        </div>
                    </div>
                    <span style="color: var(--text-dim);">→</span>
                </a>
            <?php endforeach; ?>
        </div>
        
        <?php if ($totalPages > 1): ?>
            <div style="display: flex; justify-content: center; gap: 8px; margin-top: 24px;">
                <?php if ($page > 1): ?>
                    <a href="/workouts/history?page=<?= $page - 1 ?>" class="btn btn-small" style="width: auto;">← Prev</a>
                <?php endif; ?>
                
                <span style="padding: 8px 16px; color: var(--text-dim);">
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
