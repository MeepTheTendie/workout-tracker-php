@extends('layouts.app')

@section('title', 'Continue Workout')

@section('content')
<div class="page-header">
    <div class="page-title">{{ $workout->notes ?? 'Active Workout' }}</div>
</div>

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

<div id="setsContainer">
    @if($workout->sets->isEmpty())
        <div class="section empty">No exercises added yet</div>
    @else
        @php
            $grouped = $workout->sets->groupBy('exercise_id');
        @endphp
        @foreach($grouped as $exerciseId => $sets)
            @php $exercise = $sets->first()->exercise; @endphp
            <div class="section">
                <div style="font-weight: 700; margin-bottom: 12px;">{{ strtoupper($exercise->name) }}</div>
                <div style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 8px; margin-bottom: 12px;">
                    @foreach($sets as $idx => $set)
                        <div style="padding: 8px; border: 2px solid var(--border); text-align: center;">
                            <div style="font-size: 12px; color: var(--text-dim);">Set {{ $idx + 1 }}</div>
                            <div style="font-weight: 700;">{{ $set->weight ?? 0 }}×{{ $set->reps ?? 0 }}</div>
                            <button onclick="deleteSet({{ $set->id }})" style="font-size: 10px; margin-top: 4px;">✕</button>
                        </div>
                    @endforeach
                </div>
                <div style="display: flex; gap: 8px;">
                    <input type="number" id="weight-{{ $exerciseId }}" class="form-input" placeholder="Weight" style="flex: 1;">
                    <input type="number" id="reps-{{ $exerciseId }}" class="form-input" placeholder="Reps" style="flex: 1;">
                    <button onclick="addSet({{ $exerciseId }})" class="btn" style="padding: 8px 16px;">+ Set</button>
                </div>
            </div>
        @endforeach
    @endif
</div>

<div class="section">
    <div class="form-group">
        <label class="form-label">Notes (optional)</label>
        <input type="text" id="notesInput" class="form-input" placeholder="Workout notes..." value="{{ $workout->notes }}">
    </div>
    <button id="finishBtn" class="btn btn-full">Finish Workout</button>
    <button onclick="cancelWorkout()" class="btn btn-secondary btn-full" style="margin-top: 8px;">Cancel</button>
</div>
@endsection

@section('scripts')
<script>
let workoutId = {{ $workout->id }};

function renderSets() {
    location.reload();
}

async function addSet(exerciseId) {
    const weight = document.getElementById(`weight-${exerciseId}`).value;
    const reps = document.getElementById(`reps-${exerciseId}`).value;
    
    await fetch(`/workouts/${workoutId}/sets`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
        },
        body: JSON.stringify({ exercise_id: exerciseId, weight, reps })
    });
    
    location.reload();
}

async function deleteSet(setId) {
    await fetch(`/workouts/${workoutId}/sets/${setId}`, {
        method: 'DELETE',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
        }
    });
    
    location.reload();
}

async function cancelWorkout() {
    if (!confirm('Cancel this workout?')) return;
    await fetch(`/workouts/${workoutId}`, {
        method: 'DELETE',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
        }
    });
    window.location.href = '/dashboard';
}

document.getElementById('addExerciseBtn').addEventListener('click', async () => {
    const exerciseId = document.getElementById('exerciseSelect').value;
    if (!exerciseId) return;
    
    await addSet(exerciseId);
});

document.getElementById('finishBtn').addEventListener('click', async () => {
    const notes = document.getElementById('notesInput').value;
    
    if (notes) {
        await fetch(`/workouts/${workoutId}`, {
            method: 'PATCH',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            },
            body: JSON.stringify({ notes: notes })
        });
    }
    
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
