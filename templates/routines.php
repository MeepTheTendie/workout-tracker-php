<?php
$routines = localApi('routines');
$exercises = localApi('exercises');
$id = $_GET['id'] ?? null;

if ($id) {
    $db = getDB();
    $stmt = $db->prepare("SELECT * FROM routines WHERE id = ?");
    $stmt->execute([$id]);
    $routine = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($routine) {
        $stmt = $db->prepare("
            SELECT re.*, e.name as exercise_name
            FROM routine_exercises re
            JOIN exercises e ON re.exercise_id = e.id
            WHERE re.routine_id = ?
            ORDER BY re.order_index
        ");
        $stmt->execute([$id]);
        $routine['exercises'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
?>
<div class="page-header">
    <div class="page-title"><?= $routine ? strtoupper($routine['name']) : 'ROUTINES' ?></div>
    <?php if (!$routine): ?>
        <button class="btn" onclick="document.getElementById('addRoutineForm').style.display='block'">+ New</button>
    <?php endif; ?>
</div>

<?php if (!$routine && isset($_GET['showAdd'])): ?>
    <div class="section" style="background: var(--bg);">
        <form onsubmit="return createRoutine(event)">
            <div class="form-group">
                <label class="form-label">Routine Name</label>
                <input type="text" id="routineName" class="form-input" placeholder="e.g., Push Day" required>
            </div>
            <div class="form-group">
                <label class="form-label">Description (optional)</label>
                <input type="text" id="routineDesc" class="form-input" placeholder="e.g., Chest, shoulders, triceps">
            </div>
            <button type="submit" class="btn" style="width: 100%;">CREATE ROUTINE</button>
        </form>
    </div>
<?php endif; ?>

<?php if ($routine): ?>
    <div class="section">
        <?php if (!empty($routine['description'])): ?>
            <div style="font-size: 12px; color: var(--text-dim); margin-bottom: 16px;"><?= $routine['description'] ?></div>
        <?php endif; ?>
        
        <div class="section-header">
            <span class="section-title">Exercises</span>
        </div>
        
        <?php if (empty($routine['exercises'])): ?>
            <div class="empty">No exercises added yet</div>
        <?php else: ?>
            <ul class="list">
                <?php foreach ($routine['exercises'] as $ex): ?>
                    <li class="list-item">
                        <div>
                            <div class="list-item-name"><?= strtoupper($ex['exercise_name']) ?></div>
                            <div class="list-item-meta">
                                <?= $ex['target_sets'] ?? 3 ?> sets × <?= $ex['target_reps'] ?? 8 ?> reps
                                <?= $ex['target_weight'] ? ' @ ' . $ex['target_weight'] . ' lbs' : '' ?>
                            </div>
                        </div>
                    </li>
                <?php endforeach; ?>
            </ul>
        <?php endif; ?>
        
        <button class="btn btn-secondary btn-full" style="margin-top: 16px;" onclick="startWorkout(<?= $routine['id'] ?>)">
            START THIS ROUTINE
        </button>
        
        <button class="btn btn-danger btn-full" style="margin-top: 8px;" onclick="deleteRoutine(<?= $routine['id'] ?>)">
            DELETE ROUTINE
        </button>
    </div>
<?php else: ?>
    <div class="section" style="padding: 0;">
        <?php if (empty($routines)): ?>
            <div style="padding: 20px;">
                <div class="empty">No routines yet</div>
            </div>
        <?php else: ?>
            <?php foreach ($routines as $r): ?>
                <a href="/?page=routines&id=<?= $r['id'] ?>" style="text-decoration: none; color: inherit;">
                    <div class="list-item">
                        <div>
                            <div class="list-item-name"><?= strtoupper($r['name']) ?></div>
                            <div class="list-item-meta"><?= $r['description'] ?: 'No description' ?></div>
                        </div>
                        <span class="list-item-arrow">→</span>
                    </div>
                </a>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
<?php endif; ?>

<script>
async function createRoutine(e) {
    e.preventDefault();
    const name = document.getElementById('routineName').value;
    const desc = document.getElementById('routineDesc').value;
    
    await fetch('/api/routines.php?action=routines', {
        method: 'POST',
        headers: {'Content-Type': 'application/json'},
        body: JSON.stringify({name, description: desc})
    });
    
    location.reload();
}

async function deleteRoutine(id) {
    if (!confirm('Delete this routine?')) return;
    await fetch(`/api/routines.php?action=routines&id=${id}`, {method: 'DELETE'});
    location.href = '/?page=routines';
}

async function startWorkout(routineId) {
    const res = await fetch('/api/workouts.php?action=workouts', {
        method: 'POST',
        headers: {'Content-Type': 'application/json'},
        body: JSON.stringify({routine_id: routineId, started_at: Date.now()})
    });
    const data = await res.json();
    location.href = '/?page=workout';
}
</script>
