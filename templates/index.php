<?php
$stats = localApi('stats');
$routines = localApi('routines');
$goals = localApi('goals');
?>
<div class="hero">
    <a href="/?page=workout" class="hero-btn">START WORKOUT</a>
</div>

<div class="stats-row">
    <div class="stat">
        <div class="stat-value"><?= $stats['totalWorkouts'] ?? 0 ?></div>
        <div class="stat-label">Workouts</div>
    </div>
    <div class="stat">
        <div class="stat-value"><?= formatVolume($stats['totalVolume'] ?? 0) ?></div>
        <div class="stat-label">Volume</div>
    </div>
    <div class="stat">
        <div class="stat-value"><?= $stats['avgDuration'] ?? 0 ?></div>
        <div class="stat-label">Avg Min</div>
    </div>
</div>

<div class="section">
    <div class="section-header">
        <span class="section-title">Quick Actions</span>
    </div>
    <div class="quick-grid">
        <a href="/?page=workout" class="quick-btn">+ Log Set</a>
        <a href="/?page=history" class="quick-btn">History</a>
        <a href="/?page=stats" class="quick-btn">Stats</a>
        <a href="/?page=goals" class="quick-btn">PRs</a>
    </div>
</div>

<div class="section">
    <div class="section-header">
        <span class="section-title">Routines</span>
        <a href="/?page=routines" class="section-action">See all</a>
    </div>
    <ul class="list">
        <?php if (empty($routines)): ?>
            <li class="list-item empty">
                <div class="list-item-name">No routines yet</div>
            </li>
        <?php else: ?>
            <?php foreach (array_slice($routines, 0, 3) as $routine): ?>
                <li class="list-item" onclick="location.href='/?page=routines&id=<?= $routine['id'] ?>'">
                    <div>
                        <div class="list-item-name"><?= strtoupper($routine['name']) ?></div>
                        <div class="list-item-meta"><?= $routine['description'] ?: 'No description' ?></div>
                    </div>
                    <span class="list-item-arrow">→</span>
                </li>
            <?php endforeach; ?>
        <?php endif; ?>
    </ul>
</div>

<div class="section">
    <div class="section-header">
        <span class="section-title">PR Progress</span>
        <a href="/?page=goals" class="section-action">Manage</a>
    </div>
    <?php if (empty($goals)): ?>
        <div class="empty">
            No active goals. <a href="/?page=goals">Create one →</a>
        </div>
    <?php else: ?>
        <?php foreach (array_slice($goals, 0, 3) as $goal): 
            $progress = $goal['target_weight'] > 0 ? ($goal['current_weight'] ?? 0) / $goal['target_weight'] * 100 : 0;
        ?>
            <div class="progress-item">
                <div class="progress-header">
                    <span class="progress-name"><?= strtoupper($goal['exercise_name']) ?></span>
                    <span><?= $goal['current_weight'] ?? 0 ?> / <?= $goal['target_weight'] ?> LB</span>
                </div>
                <div class="progress-track">
                    <div class="progress-fill" style="width: <?= min($progress, 100) ?>%"></div>
                </div>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>
</div>
