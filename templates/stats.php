<?php
$stats = localApi('stats');
$workouts = localApi('workouts');
?>
<div class="page-header">
    <div class="page-title">STATS</div>
</div>

<div class="stats-row">
    <div class="stat">
        <div class="stat-value"><?= $stats['totalWorkouts'] ?? 0 ?></div>
        <div class="stat-label">Total Workouts</div>
    </div>
    <div class="stat">
        <div class="stat-value"><?= formatVolume($stats['totalVolume'] ?? 0) ?></div>
        <div class="stat-label">Total Volume</div>
    </div>
    <div class="stat">
        <div class="stat-value"><?= $stats['streak'] ?? 0 ?></div>
        <div class="stat-label">Day Streak</div>
    </div>
</div>

<div class="section">
    <div class="section-header">
        <span class="section-title">Volume by Exercise</span>
    </div>
    <?php if (empty($stats['volumeByExercise'])): ?>
        <div class="empty">No data yet</div>
    <?php else: ?>
        <?php foreach ($stats['volumeByExercise'] as $ex): ?>
            <div class="progress-item">
                <div class="progress-header">
                    <span class="progress-name"><?= strtoupper($ex['exercise_name']) ?></span>
                    <span><?= formatVolume($ex['volume']) ?> lbs</span>
                </div>
                <div class="progress-track">
                    <?php 
                    $maxVol = max(array_column($stats['volumeByExercise'], 'volume'));
                    $pct = $maxVol > 0 ? ($ex['volume'] / $maxVol) * 100 : 0;
                    ?>
                    <div class="progress-fill" style="width: <?= $pct ?>%"></div>
                </div>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>
</div>

<div class="section">
    <div class="section-header">
        <span class="section-title">Last 30 Days</span>
    </div>
    <div class="stats-row" style="background: transparent; border: none;">
        <div class="stat" style="border: none;">
            <div class="stat-value"><?= $stats['recentWorkouts'] ?? 0 ?></div>
            <div class="stat-label">Workouts</div>
        </div>
        <div class="stat" style="border: none;">
            <div class="stat-value"><?= formatVolume($stats['recentVolume'] ?? 0) ?></div>
            <div class="stat-label">Volume</div>
        </div>
    </div>
</div>
