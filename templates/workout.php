<?php
$exercises = localApi('exercises');
$workouts = localApi('workouts');
$currentWorkout = null;

if (!empty($workouts)) {
    foreach ($workouts as $w) {
        if (!$w['ended_at']) {
            $currentWorkout = $w;
            break;
        }
    }
}
?>
<div class="page-header">
    <div class="page-title">LOG WORKOUT</div>
</div>

<div class="section">
    <button id="startWorkoutBtn" class="btn btn-full" <?= $currentWorkout ? 'style="display:none"' : '' ?>>
        START NEW WORKOUT
    </button>
    
    <div id="activeWorkout" <?= !$currentWorkout ? 'style="display:none"' : '' ?>>
        <div class="card" style="background: var(--bg); margin-bottom: 16px;">
            <div style="font-size: 12px; color: var(--text-dim);">
                Workout in progress • <span id="workoutDuration">0:00</span>
            </div>
        </div>
        
        <div class="form-group">
            <label class="form-label">Add Exercise</label>
            <select id="exerciseSelect" class="form-input">
                <option value="">Select exercise...</option>
                <?php foreach ($exercises as $ex): ?>
                    <option value="<?= h($ex['id']) ?>"><?= h($ex['name']) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        
        <button id="addExerciseBtn" class="btn btn-secondary btn-full">
            + ADD EXERCISE
        </button>
        
        <div id="setsContainer"></div>
        
        <button id="finishWorkoutBtn" class="btn btn-full" style="margin-top: 24px;">
            FINISH WORKOUT
        </button>
    </div>
</div>

<script>
let currentWorkoutId = <?= $currentWorkout ? $currentWorkout['id'] : 'null' ?>;
let currentSets = <?= json_encode($currentWorkout['sets'] ?? [], JSON_HEX_TAG | JSON_HEX_APOS) ?>;

function renderSets() {
    const container = document.getElementById('setsContainer');
    if (currentSets.length === 0) {
        container.innerHTML = '<div class="empty">No exercises added yet</div>';
        return;
    }
    
    const grouped = {};
    currentSets.forEach(set => {
        if (!grouped[set.exercise_name]) {
            grouped[set.exercise_name] = [];
        }
        grouped[set.exercise_name].push(set);
    });
    
    let html = '';
    for (const [exerciseName, sets] of Object.entries(grouped)) {
        const safeName = exerciseName.replace(/</g, '&lt;').replace(/>/g, '&gt;');
        html += `
            <div class="exercise-group">
                <div class="exercise-name">${safeName.toUpperCase()}</div>
                <div class="sets-grid">
        `;
        sets.forEach((set, idx) => {
            html += `
                <div class="set-card">
                    <span class="set-display">${set.weight || 0}×${set.reps || 0}</span>
                    <button class="set-btn set-btn--delete" onclick="deleteSet(${set.id})">✕</button>
                </div>
            `;
        });
        html += `
                </div>
                <div style="display: flex; gap: 8px; margin-top: 8px;">
                    <input type="number" id="weight-${sets[0].exercise_id}" class="set-input" placeholder="lbs" value="">
                    <input type="number" id="reps-${sets[0].exercise_id}" class="set-input" placeholder="reps" value="">
                    <button class="btn" style="padding: 8px 12px;" onclick="addSet(${sets[0].exercise_id})">+ SET</button>
                </div>
            </div>
        `;
    }
    container.innerHTML = html;
}

(function() {
    const btn = document.getElementById('startWorkoutBtn');
    if (!btn) {
        console.error('Start workout button not found in DOM!');
        return;
    }
    console.log('Start workout button found, attaching listener');
    
    btn.addEventListener('click', async function(e) {
        console.log('Start workout clicked');
        e.preventDefault();
        
        try {
            const res = await fetch('/api/workouts.php?action=workouts', {
                method: 'POST',
                headers: {'Content-Type': 'application/json'},
                body: JSON.stringify({started_at: Date.now()})
            });
            console.log('Response status:', res.status);
            
            const text = await res.text();
            console.log('Response text:', text);
            
            if (!res.ok) {
                console.error('Error response:', text);
                throw new Error(`HTTP error! status: ${res.status}, body: ${text || '(empty)'}`);
            }
            
            let data;
            try {
                data = JSON.parse(text);
            } catch (parseErr) {
                throw new Error(`Invalid JSON response: ${text || '(empty)'}`);
            }
            console.log('Response data:', data);
            
            if (!data.id) {
                throw new Error('No workout ID returned from server: ' + JSON.stringify(data));
            }
            
            currentWorkoutId = parseInt(data.id);
            document.getElementById('startWorkoutBtn').style.display = 'none';
            document.getElementById('activeWorkout').style.display = 'block';
            renderSets();
            startTimer();
            console.log('Workout started successfully');
        } catch (err) {
            console.error('Failed to start workout:', err);
            alert('Failed to start workout: ' + err.message);
        }
    });
})();

document.getElementById('addExerciseBtn')?.addEventListener('click', async () => {
    const exerciseId = document.getElementById('exerciseSelect').value;
    if (!exerciseId || !currentWorkoutId) return;
    
    try {
        const res = await fetch('/api/workouts.php?action=sets', {
            method: 'POST',
            headers: {'Content-Type': 'application/json'},
            body: JSON.stringify({
                workout_id: currentWorkoutId,
                exercise_id: exerciseId,
                set_number: 1,
                completed_at: Date.now()
            })
        });
        if (!res.ok) {
            throw new Error(`HTTP error! status: ${res.status}`);
        }
        const data = await res.json();
        if (!data.id) {
            throw new Error('No set ID returned from server');
        }
        currentSets.push({
            id: data.id,
            exercise_id: parseInt(exerciseId),
            exercise_name: document.getElementById('exerciseSelect').options[document.getElementById('exerciseSelect').selectedIndex].text,
            weight: 0,
            reps: 0
        });
        renderSets();
    } catch (err) {
        console.error('Failed to add exercise:', err);
        alert('Failed to add exercise. Please try again.');
    }
});

async function addSet(exerciseId) {
    const weight = document.getElementById(`weight-${exerciseId}`).value;
    const reps = document.getElementById(`reps-${exerciseId}`).value;
    
    const res = await fetch('/api/workouts.php?action=sets', {
        method: 'POST',
        headers: {'Content-Type': 'application/json'},
        body: JSON.stringify({
            workout_id: currentWorkoutId,
            exercise_id: exerciseId,
            set_number: 1,
            weight: parseFloat(weight) || 0,
            reps: parseInt(reps) || 0,
            completed_at: Date.now()
        })
    });
    const data = await res.json();
    currentSets.push({
        id: data.id,
        exercise_id: exerciseId,
        exercise_name: currentSets.find(s => s.exercise_id === exerciseId)?.exercise_name || '',
        weight: parseFloat(weight) || 0,
        reps: parseInt(reps) || 0
    });
    renderSets();
}

async function deleteSet(setId) {
    await fetch(`/api/workouts.php?action=sets&id=${setId}`, {method: 'DELETE'});
    currentSets = currentSets.filter(s => s.id !== setId);
    renderSets();
}

document.getElementById('finishWorkoutBtn')?.addEventListener('click', async () => {
    try {
        const res = await fetch('/api/workouts.php?action=workouts', {
            method: 'PATCH',
            headers: {'Content-Type': 'application/json'},
            body: JSON.stringify({id: currentWorkoutId, ended_at: Date.now()})
        });
        if (!res.ok) {
            throw new Error(`HTTP error! status: ${res.status}`);
        }
        location.href = '/?page=history';
    } catch (err) {
        console.error('Failed to finish workout:', err);
        alert('Failed to finish workout. Please try again.');
    }
});

function startTimer() {
    const start = <?= $currentWorkout ? $currentWorkout['started_at'] : 'Date.now()' ?>;
    setInterval(() => {
        const elapsed = Math.floor((Date.now() - start) / 1000);
        const mins = Math.floor(elapsed / 60);
        const secs = elapsed % 60;
        document.getElementById('workoutDuration').textContent = `${mins}:${secs.toString().padStart(2, '0')}`;
    }, 1000);
}

renderSets();
<?php if ($currentWorkout): ?>
startTimer();
<?php endif; ?>
</script>
