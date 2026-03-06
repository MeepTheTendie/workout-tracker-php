<?php
$goals = localApi('goals');
$exercises = localApi('exercises');

$db = getDB();
$stmt = $db->query("SELECT g.*, e.name as exercise_name FROM goals g JOIN exercises e ON g.exercise_id = e.id WHERE g.completed = 1 ORDER BY g.created_at DESC");
$completedGoals = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<div class="page-header">
    <div class="page-title">PR GOALS</div>
    <button class="btn" onclick="toggleAddForm()">
        <?= isset($_GET['showAdd']) ? 'Cancel' : 'Add Goal' ?>
    </button>
</div>

<?php if (isset($_GET['showAdd'])): ?>
    <div class="section" style="background: var(--bg);">
        <form method="post" action="/api/goals.php?action=goals" onsubmit="return handleGoalSubmit(event)">
            <div class="form-group">
                <label class="form-label">Exercise</label>
                <select name="exercise_id" class="form-input" required>
                    <option value="">Select exercise...</option>
                    <?php foreach ($exercises as $ex): ?>
                        <option value="<?= $ex['id'] ?>"><?= $ex['name'] ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            
            <div style="display: flex; gap: 12px; margin-bottom: 16px;">
                <div class="form-group" style="flex: 1;">
                    <label class="form-label">Target Weight (lbs)</label>
                    <input type="number" name="target_weight" class="form-input" placeholder="0" required>
                </div>
                <div class="form-group" style="flex: 1;">
                    <label class="form-label">Target Reps</label>
                    <input type="number" name="target_reps" class="form-input" placeholder="1" value="1">
                </div>
            </div>
            
            <button type="submit" class="btn" style="width: 100%;">CREATE GOAL</button>
        </form>
    </div>
<?php endif; ?>

<div class="section">
    <div class="section-header">
        <span class="section-title">Active Goals</span>
    </div>
    
    <?php if (empty($goals)): ?>
        <div class="empty">No active goals. Click "Add Goal" to create one.</div>
    <?php else: ?>
        <?php foreach ($goals as $goal): 
            $progress = $goal['target_weight'] > 0 ? ($goal['current_weight'] ?? 0) / $goal['target_weight'] * 100 : 0;
        ?>
            <div class="card" style="position: relative;">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 8px;">
                    <div style="font-weight: 700; font-size: 14px;"><?= strtoupper($goal['exercise_name']) ?></div>
                    <button class="btn btn-secondary" style="padding: 8px 12px; font-size: 10px;" onclick="deleteGoal(<?= $goal['id'] ?>)">Delete</button>
                </div>
                
                <div style="display: flex; justify-content: space-between; font-size: 12px; margin-bottom: 8px;">
                    <span>Current: <strong><?= $goal['current_weight'] ?? 0 ?></strong> lbs</span>
                    <span>Target: <strong><?= $goal['target_weight'] ?></strong> lbs</span>
                </div>
                
                <div class="progress-track">
                    <div class="progress-fill" style="width: <?= min($progress, 100) ?>%; background: <?= $progress >= 100 ? 'var(--accent)' : 'var(--border)' ?>"></div>
                </div>
                
                <?php if ($progress >= 100): ?>
                    <button class="btn" style="margin-top: 12px; width: 100%;" onclick="completeGoal(<?= $goal['id'] ?>)">Mark Complete!</button>
                <?php endif; ?>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>
</div>

<?php if (!empty($completedGoals)): ?>
    <div class="section">
        <div class="section-header">
            <span class="section-title">Completed</span>
        </div>
        
        <?php foreach ($completedGoals as $goal): ?>
            <div class="card" style="opacity: 0.6;">
                <div style="display: flex; justify-content: space-between; align-items: center;">
                    <div>
                        <div style="font-weight: 700; font-size: 14px; text-decoration: line-through;"><?= strtoupper($goal['exercise_name']) ?></div>
                        <div style="font-size: 11px; color: var(--text-dim);">Achieved: <?= $goal['target_weight'] ?> lbs × <?= $goal['target_reps'] ?> reps</div>
                    </div>
                    <button class="btn btn-secondary" style="padding: 8px 12px; font-size: 10px;" onclick="deleteGoal(<?= $goal['id'] ?>)">Delete</button>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
<?php endif; ?>

<script>
function toggleAddForm() {
    const url = new URL(window.location.href);
    if (url.searchParams.get('showAdd')) {
        url.searchParams.delete('showAdd');
    } else {
        url.searchParams.set('showAdd', '1');
    }
    window.location.href = url;
}

async function handleGoalSubmit(e) {
    e.preventDefault();
    const form = e.target;
    const data = {
        exercise_id: form.exercise_id.value,
        target_weight: parseFloat(form.target_weight.value),
        target_reps: parseInt(form.target_reps.value) || 1
    };
    
    await fetch('/api/goals.php?action=goals', {
        method: 'POST',
        headers: {'Content-Type': 'application/json'},
        body: JSON.stringify(data)
    });
    
    location.reload();
}

async function deleteGoal(id) {
    if (!confirm('Delete this goal?')) return;
    await fetch(`/api/goals.php?action=goals&id=${id}`, {method: 'DELETE'});
    location.reload();
}

async function completeGoal(id) {
    await fetch('/api/goals.php?action=goals', {
        method: 'PATCH',
        headers: {'Content-Type': 'application/json'},
        body: JSON.stringify({id: id, completed: true})
    });
    location.reload();
}
</script>
