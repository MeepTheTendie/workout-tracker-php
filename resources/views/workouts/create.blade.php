@extends('layouts.app')

@section('title', 'New Workout')

@section('content')
<div class="page-header">
    <div class="page-title">New Workout</div>
</div>

<div class="section">
    <button id="startBtn" class="btn btn-full">Start Workout</button>
</div>

<div id="workoutForm" style="display: none;">
    <div class="section">
        <div class="form-group">
            <label class="form-label">Add Exercise</label>
            <select id="exerciseSelect" class="form-input">
                <option value="">Select exercise...</option>
                @foreach($exercises as $exercise)
                    <option value="{{ $exercise->id }}">{{ $exercise->name }}</option>
                @endforeach
            </select>
        </div>
        <button id="addExerciseBtn" class="btn btn-secondary btn-full">+ Add Exercise</button>
    </div>
    
    <div id="setsContainer"></div>
    
    <div class="section">
        <button id="finishBtn" class="btn btn-full">Finish Workout</button>
    </div>
</div>
@endsection

@section('scripts')
<script>
let workoutId = null;
let currentSets = [];

function renderSets() {
    const container = document.getElementById('setsContainer');
    if (currentSets.length === 0) {
        container.innerHTML = '<div class="section empty">No exercises added yet</div>';
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
    for (const [name, sets] of Object.entries(grouped)) {
        const exerciseId = sets[0].exercise_id;
        html += `
            <div class="section">
                <div style="font-weight: 700; margin-bottom: 12px;">${name.toUpperCase()}</div>
                <div style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 8px; margin-bottom: 12px;">
                    ${sets.map((set, idx) => `
                        <div style="padding: 8px; border: 2px solid var(--border); text-align: center;">
                            <div style="font-size: 12px; color: var(--text-dim);">Set ${idx + 1}</div>
                            <div style="font-weight: 700;">${set.weight || 0}×${set.reps || 0}</div>
                            <button onclick="deleteSet(${set.id})" style="font-size: 10px; margin-top: 4px;">✕</button>
                        </div>
                    `).join('')}
                </div>
                <div style="display: flex; gap: 8px;">
                    <input type="number" id="weight-${exerciseId}" class="form-input" placeholder="Weight" style="flex: 1;">
                    <input type="number" id="reps-${exerciseId}" class="form-input" placeholder="Reps" style="flex: 1;">
                    <button onclick="addSet(${exerciseId})" class="btn" style="padding: 8px 16px;">+ Set</button>
                </div>
            </div>
        `;
    }
    container.innerHTML = html;
}

async function addSet(exerciseId) {
    const weight = document.getElementById(`weight-${exerciseId}`).value;
    const reps = document.getElementById(`reps-${exerciseId}`).value;
    
    const res = await fetch(`/workouts/${workoutId}/sets`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
        },
        body: JSON.stringify({ exercise_id: exerciseId, weight, reps })
    });
    
    const data = await res.json();
    const exerciseName = document.querySelector(`#exerciseSelect option[value="${exerciseId}"]`).text;
    
    currentSets.push({
        id: data.id,
        exercise_id: exerciseId,
        exercise_name: exerciseName,
        weight: parseFloat(weight) || 0,
        reps: parseInt(reps) || 0
    });
    
    renderSets();
}

async function deleteSet(setId) {
    await fetch(`/workouts/${workoutId}/sets/${setId}`, {
        method: 'DELETE',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
        }
    });
    
    currentSets = currentSets.filter(s => s.id !== setId);
    renderSets();
}

document.getElementById('startBtn').addEventListener('click', async () => {
    const res = await fetch('/workouts', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
        }
    });
    
    const data = await res.json();
    workoutId = data.id;
    
    document.getElementById('startBtn').style.display = 'none';
    document.getElementById('workoutForm').style.display = 'block';
    renderSets();
});

document.getElementById('addExerciseBtn').addEventListener('click', async () => {
    const exerciseId = document.getElementById('exerciseSelect').value;
    if (!exerciseId) return;
    
    await addSet(exerciseId);
});

document.getElementById('finishBtn').addEventListener('click', async () => {
    await fetch(`/workouts/${workoutId}`, {
        method: 'PATCH',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
        },
        body: JSON.stringify({ ended_at: true })
    });
    
    window.location.href = '/dashboard';
});
</script>
@endsection
