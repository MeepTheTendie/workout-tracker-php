<?php
/**
 * Goals Page - Redesigned
 */

requireAuth();

$goals = dbFetchAll("SELECT * FROM goals WHERE user_id = ? ORDER BY created_at DESC", [currentUserId()]);

renderPage('Goals', function() use ($goals) {
?>
<h1>GOALS</h1>

<?php if (empty($goals)): ?>
    <div class="goals-empty">
        <div class="goals-empty-text">No goals set yet.</div>
        <div class="goals-empty-subtext">Set a personal record, hit a workout count, or achieve a volume target to see it here.</div>
        <button class="btn btn-primary" onclick="alert('Goals feature coming soon!')">+ ADD YOUR FIRST GOAL</button>
    </div>
<?php else: ?>
    <?php foreach ($goals as $goal): 
        $progress = $goal['target_value'] > 0 ? ($goal['current_value'] / $goal['target_value'] * 100) : 0;
        $progress = min(100, max(0, $progress));
    ?>
        <div class="card">
            <div style="font-size: 14px; font-weight: 700; text-transform: uppercase; margin-bottom: 12px;"><?= e($goal['title']) ?></div>
            <div style="background: var(--border); border-radius: 4px; height: 8px; margin: 12px 0;">
                <div style="background: var(--accent); height: 100%; border-radius: 4px; width: <?= $progress ?>%;"></div>
            </div>
            <div style="display: flex; justify-content: space-between; font-size: 12px; color: var(--text-dim);">
                <span><?= $goal['current_value'] ?> <?= e($goal['unit']) ?></span>
                <span><?= $goal['target_value'] ?> <?= e($goal['unit']) ?></span>
            </div>
        </div>
    <?php endforeach; ?>
<?php endif; ?>
<?php
});
