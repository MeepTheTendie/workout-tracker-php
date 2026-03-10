<?php
$workouts = localApi('workouts');

function formatDate($timestamp) {
    return date('M j, Y', $timestamp / 1000);
}

function formatDuration($started, $ended) {
    if (!$ended) return 'In progress';
    $mins = round(($ended - $started) / 60000);
    return "$mins min";
}

function getRelativeDate($timestamp) {
    $now = time() * 1000;
    $diff = (int) floor(($now - $timestamp) / 86400000);
    
    if ($diff === 0) return 'Today';
    if ($diff === 1) return 'Yesterday';
    if ($diff < 7) return "$diff days ago";
    if ($diff < 30) return floor($diff / 7) . ' weeks ago';
    return null;
}

$grouped = [];
foreach ($workouts as $w) {
    $month = date('F Y', $w['started_at'] / 1000);
    if (!isset($grouped[$month])) $grouped[$month] = [];
    $grouped[$month][] = $w;
}

$stats = [
    'totalWorkouts' => count($workouts),
    'totalVolume' => array_sum(array_column($workouts, 'volume')),
    'totalSets' => array_sum(array_map(fn($w) => count($w['sets']), $workouts))
];
?>
<div class="page-header">
    <div class="page-title">Workout History</div>
</div>

<div class="stats-row">
    <div class="stat">
        <div class="stat-value"><?= $stats['totalWorkouts'] ?></div>
        <div class="stat-label">Workouts</div>
    </div>
    <div class="stat">
        <div class="stat-value"><?= formatVolume($stats['totalVolume']) ?></div>
        <div class="stat-label">Lbs Volume</div>
    </div>
    <div class="stat">
        <div class="stat-value"><?= $stats['totalSets'] ?></div>
        <div class="stat-label">Sets</div>
    </div>
</div>

<div class="section" style="padding: 0;">
    <?php if (empty($workouts)): ?>
        <div style="padding: 20px;">
            <div class="empty">
                No workouts yet. <a href="/?page=workout">Start one now →</a>
            </div>
        </div>
    <?php else: ?>
        <?php foreach ($grouped as $month => $monthWorkouts): ?>
            <div>
                <div style="
                    background: var(--bg);
                    padding: 12px 20px;
                    border-bottom: 1px solid var(--border);
                    font-size: 10px;
                    text-transform: uppercase;
                    letter-spacing: 2px;
                    color: var(--text-dim);
                    font-weight: 700;
                    display: flex;
                    justify-content: space-between;
                    align-items: center
                ">
                    <span><?= $month ?></span>
                    <span><?= count($monthWorkouts) ?> workouts</span>
                </div>
                
                <?php foreach ($monthWorkouts as $w): 
                    $relativeDate = getRelativeDate($w['started_at']);
                ?>
                    <a href="/?page=workout_detail&id=<?= $w['id'] ?>" style="text-decoration: none; color: inherit; display: block;">
                        <div style="
                            padding: 16px 20px;
                            border-bottom: 1px solid var(--border);
                            cursor: pointer;
                        ">
                            <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 8px;">
                                <div style="display: flex; align-items: center; gap: 8px;">
                                    <div style="font-weight: 700; font-size: 14px;">
                                        <?= formatDate($w['started_at']) ?>
                                    </div>
                                    <span style="font-size: 10px; color: var(--text-dim); background: var(--bg); padding: 2px 6px; border-radius: 4px;">
                                        <?= date('D', $w['started_at'] / 1000) ?>
                                    </span>
                                    <?php if ($relativeDate): ?>
                                        <span style="font-size: 10px; color: var(--accent); font-weight: 700;">
                                            <?= $relativeDate ?>
                                        </span>
                                    <?php endif; ?>
                                </div>
                                <div style="font-size: 11px; color: var(--text-dim);">
                                    <?= formatDuration($w['started_at'], $w['ended_at']) ?>
                                </div>
                            </div>
                            
                            <div style="display: flex; gap: 16px; font-size: 12px; margin-bottom: 8px;">
                                <div><span style="font-weight: 700;"><?= count($w['sets']) ?></span> sets</div>
                                <div><span style="font-weight: 700;"><?= number_format(round($w['volume'])) ?></span> lbs volume</div>
                            </div>
                            
                            <?php if (count($w['sets']) > 0): ?>
                                <div style="font-size: 11px; color: var(--text-dim); display: flex; flex-wrap: wrap; gap: 4px;">
                                    <?php foreach (array_unique(array_column($w['sets'], 'exercise_name')) as $ex): ?>
                                        <span><?= $ex ?></span><?= ', ' ?>
                                    <?php endforeach; ?>
                                </div>
                            <?php endif; ?>
                        </div>
                    </a>
                <?php endforeach; ?>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>
</div>
