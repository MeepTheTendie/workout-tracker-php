@extends('layouts.app')

@section('title', $routine->name)

@section('content')
@php
$exercises = App\Models\Exercise::orderBy('name')->get();
@endphp

<div class="page-header">
    <div class="page-title">{{ strtoupper($routine->name) }}</div>
    <a href="/routines" class="btn" style="padding: 8px 16px;">← Back</a>
</div>

<div class="section">
    @if($routine->description)
        <div style="font-size: 12px; color: var(--text-dim); margin-bottom: 16px;">{{ $routine->description }}</div>
    @endif

    <div style="font-size: 12px; font-weight: 700; text-transform: uppercase; margin-bottom: 16px;">Exercises</div>
    
    <div id="addExerciseForm" style="display:none; margin-bottom: 16px; padding: 12px; background: var(--bg); border: 2px solid var(--border);">
        <select id="newExerciseId" class="form-input" style="margin-bottom: 8px;">
            <option value="">Select exercise...</option>
            @foreach($exercises as $ex)
                <option value="{{ $ex->id }}">{{ $ex->name }}</option>
            @endforeach
        </select>
        <div style="display: flex; gap: 8px; margin-bottom: 8px;">
            <input type="number" id="targetSets" class="form-input" placeholder="Sets" value="3" style="flex:1">
            <input type="number" id="targetReps" class="form-input" placeholder="Reps" value="8" style="flex:1">
            <input type="number" id="targetWeight" class="form-input" placeholder="Lbs" style="flex:1" step="0.5">
        </div>
        <button class="btn" style="width: 100%;" onclick="addExercise()">ADD</button>
        <button class="btn btn-secondary" style="width: 100%; margin-top: 8px;" onclick="toggleAddExercise()">CANCEL</button>
    </div>
    
    @if($routine->exercises->isEmpty())
        <div class="empty">No exercises added yet</div>
    @else
        @foreach($routine->exercises as $ex)
            <div style="padding: 12px; border-bottom: 1px solid var(--border);">
                <div style="font-weight: 700;">{{ strtoupper($ex->exercise->name) }}</div>
                <div style="font-size: 12px; color: var(--text-dim);">
                    {{ $ex->target_sets ?? 3 }} sets × {{ $ex->target_reps ?? 8 }} reps
                    @if($ex->target_weight)
                        @ {{ $ex->target_weight }} lbs
                    @endif
                </div>
            </div>
        @endforeach
    @endif
    
    <button class="btn btn-secondary" style="width: 100%; margin-top: 16px;" onclick="toggleAddExercise()">
        + Add Exercise
    </button>
    
    <button class="btn" style="width: 100%; margin-top: 16px;" onclick="startWorkout()">
        START THIS ROUTINE
    </button>
    
    <button class="btn btn-secondary" style="width: 100%; margin-top: 8px; border-color: #ff4444; color: #ff4444;" onclick="deleteRoutine()">
        DELETE ROUTINE
    </button>
</div>
@endsection

@section('scripts')
<script>
function toggleAddExercise() {
    const form = document.getElementById('addExerciseForm');
    form.style.display = form.style.display === 'none' ? 'block' : 'none';
}

async function addExercise() {
    const exerciseId = document.getElementById('newExerciseId').value;
    const targetSets = document.getElementById('targetSets').value;
    const targetReps = document.getElementById('targetReps').value;
    const targetWeight = document.getElementById('targetWeight').value;
    
    if (!exerciseId) {
        alert('Please select an exercise');
        return;
    }
    
    await fetch('/routines/{{ $routine->id }}/exercises', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
        },
        body: JSON.stringify({
            exercise_id: exerciseId,
            target_sets: parseInt(targetSets) || 3,
            target_reps: parseInt(targetReps) || 8,
            target_weight: parseFloat(targetWeight) || null
        })
    });
    
    location.reload();
}

async function startWorkout() {
    const res = await fetch('/workouts', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
        },
        body: JSON.stringify({ routine_id: {{ $routine->id }} })
    });
    const data = await res.json();
    window.location.href = '/workouts/create';
}

async function deleteRoutine() {
    if (!confirm('Delete this routine?')) return;
    await fetch('/routines/{{ $routine->id }}', {
        method: 'DELETE',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
        }
    });
    window.location.href = '/routines';
}
</script>
@endsection
