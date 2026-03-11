<?php
$routines = localApi('routines');
$exercises = localApi('exercises');
$id = $_GET['id'] ?? null;
$routine = null;

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
    <div class="page-title"><?= $routine ? strtoupper(h($routine['name'])) : 'ROUTINES' ?></div>
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
                <div style="font-size: 12px; color: var(--text-dim); margin-bottom: 16px;"><?= h($routine['description']) ?></div>
            <?php endif; ?>
        
        <div class="section-header">
            <span class="section-title">Exercises</span>
            <button class="section-action" onclick="document.getElementById('addExerciseForm').style.display='block'">+ Add</button>
        </div>
        
        <div id="addExerciseForm" style="display:none; margin-bottom: 16px; padding: 12px; background: var(--bg); border: 2px solid var(--border);">
            <select id="newExerciseId" class="form-input" style="margin-bottom: 8px;">
                <option value="">Select exercise...</option>
                <?php foreach ($exercises as $ex): ?>
                    <option value="<?= h($ex['id']) ?>"><?= h($ex['name']) ?></option>
                <?php endforeach; ?>
            </select>
            <div style="display: flex; gap: 8px; margin-bottom: 8px;">
                <input type="number" id="targetSets" class="form-input" placeholder="Sets" value="3" style="flex:1">
                <input type="number" id="targetReps" class="form-input" placeholder="Reps" value="8" style="flex:1">
                <input type="number" id="targetWeight" class="form-input" placeholder="Lbs" style="flex:1">
            </div>
            <button class="btn" style="width: 100%;" onclick="addExerciseToRoutine(<?= h($routine['id']) ?>)">ADD</button>
        </div>
        
        <?php if (empty($routine['exercises'])): ?>
            <div class="empty">No exercises added yet</div>
        <?php else: ?>
            <ul class="list">
                <?php foreach ($routine['exercises'] as $ex): ?>
                    <li class="list-item">
                        <div>
                            <div class="list-item-name"><?= strtoupper(h($ex['exercise_name'])) ?></div>
                            <div class="list-item-meta">
                                <?= h($ex['target_sets'] ?? 3) ?> sets × <?= h($ex['target_reps'] ?? 8) ?> reps
                                <?= $ex['target_weight'] ? ' @ ' . h($ex['target_weight']) . ' lbs' : '' ?>
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
                <a href="/?page=routines&id=<?= h($r['id']) ?>" style="text-decoration: none; color: inherit;">
                    <div class="list-item">
                        <div>
                            <div class="list-item-name"><?= strtoupper(h($r['name'])) ?></div>
                            <div class="list-item-meta"><?= h($r['description'] ?: 'No description') ?></div>
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

async function addExerciseToRoutine(routineId) {
    const exerciseId = document.getElementById('newExerciseId').value;
    const targetSets = document.getElementById('targetSets').value;
    const targetReps = document.getElementById('targetReps').value;
    const targetWeight = document.getElementById('targetWeight').value;
    
    if (!exerciseId) {
        alert('Please select an exercise');
        return;
    }
    
    await fetch('/api/routines.php?action=routine-exercises', {
        method: 'POST',
        headers: {'Content-Type': 'application/json'},
        body: JSON.stringify({
            routine_id: routineId,
            exercise_id: exerciseId,
            order_index: 0,
            target_sets: parseInt(targetSets) || 3,
            target_reps: parseInt(targetReps) || 8,
            target_weight: parseFloat(targetWeight) || null
        })
    });
    
    location.reload();
}
</script>
