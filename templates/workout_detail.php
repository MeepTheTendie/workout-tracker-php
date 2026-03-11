<?php
$id = $_GET['id'] ?? null;

if (!$id) {
    header('Location: /?page=history');
    exit;
}

$db = getDB();
$stmt = $db->prepare("SELECT * FROM workouts WHERE id = ?");
$stmt->execute([$id]);
$workout = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$workout) {
    header('Location: /?page=history');
    exit;
}

$stmt = $db->prepare("
    SELECT ws.*, e.name as exercise_name 
    FROM workout_sets ws 
    JOIN exercises e ON ws.exercise_id = e.id 
    WHERE ws.workout_id = ? 
    ORDER BY ws.completed_at
");
$stmt->execute([$id]);
$sets = $stmt->fetchAll(PDO::FETCH_ASSOC);

$workout['sets'] = $sets;
$workout['volume'] = array_reduce($sets, fn($sum, $s) => $sum + (($s['weight'] ?? 0) * ($s['reps'] ?? 0)), 0);

function formatDate($timestamp) {
    return date('l, M j, Y', $timestamp / 1000);
}

function formatDuration($started, $ended) {
    if (!$ended) return 'In progress';
    $mins = round(($ended - $started) / 60000);
    return "$mins minutes";
}
?>
<div class="page-header">
    <div class="page-title">WORKOUT</div>
    <a href="/?page=history" class="btn" style="padding: 8px 16px;">← Back</a>
</div>

<div class="section">
    <div style="font-size: 18px; font-weight: 700; margin-bottom: 8px;">
        <?= formatDate($workout['started_at']) ?>
    </div>
    <div style="font-size: 12px; color: var(--text-dim); margin-bottom: 16px;">
        Duration: <?= formatDuration($workout['started_at'], $workout['ended_at']) ?>
    </div>
    
    <div class="stats-row" style="background: transparent; border: 2px solid var(--border);">
        <div class="stat" style="border-right: 1px solid var(--border);">
            <div class="stat-value"><?= count($sets) ?></div>
            <div class="stat-label">Sets</div>
        </div>
        <div class="stat">
            <div class="stat-value"><?= number_format(round($workout['volume'])) ?></div>
            <div class="stat-label">Lbs Volume</div>
        </div>
    </div>
</div>

<div class="section">
    <div class="section-header">
        <span class="section-title">Exercises</span>
    </div>
    
    <?php 
    $grouped = [];
    foreach ($sets as $s) {
        if (!isset($grouped[$s['exercise_name']])) {
            $grouped[$s['exercise_name']] = [];
        }
        $grouped[$s['exercise_name']][] = $s;
    }
    ?>
    
    <?php foreach ($grouped as $exerciseName => $exSets): ?>
        <div class="exercise-group">
            <div class="exercise-name"><?= strtoupper(h($exerciseName)) ?></div>
            <div class="sets-grid">
                <?php foreach ($exSets as $idx => $set): ?>
                    <div class="set-card">
                        <span class="set-display"><?= h($set['weight'] ?? 0) ?>×<?= h($set['reps'] ?? 0) ?></span>
                        <span style="color: var(--text-dim); font-size: 10px;">set <?= $idx + 1 ?></span>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    <?php endforeach; ?>
    
    <?php if (empty($sets)): ?>
        <div class="empty">No exercises recorded</div>
    <?php endif; ?>
</div>

<?php if ($workout['notes']): ?>
<div class="section">
    <div class="section-header">
        <span class="section-title">Notes</span>
    </div>
    <div style="font-size: 14px;"><?= h($workout['notes']) ?></div>
</div>
<?php endif; ?>
