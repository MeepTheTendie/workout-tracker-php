@extends('layouts.app')

@section('title', 'Active Workout')

@section('styles')
<style>
    .active-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        margin-bottom: 20px;
        padding-bottom: 16px;
        border-bottom: 1px solid var(--border);
    }
    
    .active-status {
        display: flex;
        align-items: center;
        gap: 8px;
        font-size: 11px;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 1px;
        color: var(--accent);
    }
    
    .active-status-dot {
        width: 8px;
        height: 8px;
        background: var(--accent);
        border-radius: 50%;
        animation: pulse 2s infinite;
    }
    
    @keyframes pulse {
        0%, 100% { opacity: 1; }
        50% { opacity: 0.5; }
    }
    
    .active-timer {
        font-size: 20px;
        font-weight: 700;
        font-family: 'Space Mono', monospace;
    }
    
    .add-exercise-section {
        background: var(--surface);
        border: 1px solid var(--border);
        border-radius: 12px;
        padding: 16px;
        margin-bottom: 16px;
    }
    
    .add-exercise-label {
        font-size: 11px;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 1px;
        color: var(--text-dim);
        margin-bottom: 10px;
    }
    
    .exercise-select-row {
        display: flex;
        gap: 10px;
    }
    
    .exercise-select {
        flex: 1;
    }
    
    .btn-add {
        display: flex;
        align-items: center;
        justify-content: center;
        width: 48px;
        background: var(--accent);
        border: none;
        border-radius: 8px;
        color: #fff;
        cursor: pointer;
        transition: all 0.2s;
    }
    
    .btn-add:hover {
        background: var(--accent-hover);
    }
    
    .btn-add svg {
        width: 20px;
        height: 20px;
    }
    
    /* Exercise Cards */
    .exercise-card {
        background: var(--surface);
        border: 1px solid var(--border);
        border-radius: 12px;
        margin-bottom: 16px;
        overflow: hidden;
    }
    
    .exercise-card-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 16px;
        background: var(--surface-hover);
    }
    
    .exercise-card-title {
        display: flex;
        align-items: center;
        gap: 10px;
        font-size: 14px;
        font-weight: 700;
        text-transform: uppercase;
    }
    
    .exercise-card-icon {
        width: 32px;
        height: 32px;
        background: var(--bg);
        border-radius: 8px;
        display: flex;
        align-items: center;
        justify-content: center;
    }
    
    .exercise-card-icon svg {
        width: 18px;
        height: 18px;
        color: var(--accent);
    }
    
    .exercise-set-count {
        font-size: 11px;
        color: var(--text-dim);
        background: var(--bg);
        padding: 4px 10px;
        border-radius: 12px;
    }
    
    .exercise-card-body {
        padding: 12px 16px;
    }
    
    /* Sets Grid */
    .sets-grid {
        display: grid;
        grid-template-columns: repeat(3, 1fr);
        gap: 8px;
        margin-bottom: 12px;
    }
    
    .set-box {
        background: var(--bg);
        border: 1px solid var(--border);
        border-radius: 8px;
        padding: 10px;
        text-align: center;
        position: relative;
    }
    
    .set-box-number {
        font-size: 10px;
        color: var(--text-dim);
        margin-bottom: 4px;
    }
    
    .set-box-value {
        font-size: 14px;
        font-weight: 700;
    }
    
    .set-box-delete {
        position: absolute;
        top: 4px;
        right: 4px;
        width: 18px;
        height: 18px;
        background: transparent;
        border: none;
        color: var(--text-dim);
        cursor: pointer;
        opacity: 0;
        transition: all 0.2s;
    }
    
    .set-box:hover .set-box-delete {
        opacity: 1;
    }
    
    .set-box-delete:hover {
        color: #ff6b6b;
    }
    
    /* Add Set Row */
    .add-set-row {
        display: flex;
        gap: 8px;
    }
    
    .add-set-input {
        flex: 1;
    }
    
    .add-set-input input {
        width: 100%;
        padding: 12px;
        background: var(--bg);
        border: 1px solid var(--border);
        border-radius: 8px;
        font-family: 'Space Mono', monospace;
        font-size: 14px;
        color: var(--text);
        text-align: center;
    }
    
    .add-set-input input::placeholder {
        color: var(--text-muted);
    }
    
    .add-set-btn {
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 12px 20px;
        background: var(--accent);
        border: none;
        border-radius: 8px;
        color: #fff;
        font-family: 'Space Mono', monospace;
        font-size: 12px;
        font-weight: 700;
        text-transform: uppercase;
        cursor: pointer;
        transition: all 0.2s;
    }
    
    .add-set-btn:hover {
        background: var(--accent-hover);
    }
    
    /* Notes Section */
    .notes-section {
        background: var(--surface);
        border: 1px solid var(--border);
        border-radius: 12px;
        padding: 16px;
        margin-bottom: 16px;
    }
    
    .notes-label {
        font-size: 11px;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 1px;
        color: var(--text-dim);
        margin-bottom: 10px;
    }
    
    .notes-input {
        width: 100%;
        padding: 12px;
        background: var(--bg);
        border: 1px solid var(--border);
        border-radius: 8px;
        font-family: 'Space Mono', monospace;
        font-size: 14px;
        color: var(--text);
        resize: vertical;
        min-height: 80px;
    }
    
    .notes-input:focus {
        outline: none;
        border-color: var(--accent);
    }
    
    /* Action Buttons */
    .action-buttons {
        display: flex;
        flex-direction: column;
        gap: 10px;
    }
    
    .btn-finish {
        width: 100%;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
        padding: 16px;
        background: var(--success);
        border: none;
        border-radius: 10px;
        color: #000;
        font-family: 'Space Mono', monospace;
        font-size: 14px;
        font-weight: 700;
        text-transform: uppercase;
        cursor: pointer;
        transition: all 0.2s;
    }
    
    .btn-finish:hover {
        filter: brightness(1.1);
    }
    
    .btn-finish svg {
        width: 18px;
        height: 18px;
    }
    
    .btn-cancel {
        width: 100%;
        padding: 14px;
        background: transparent;
        border: 1px solid var(--border);
        border-radius: 10px;
        color: var(--text-dim);
        font-family: 'Space Mono', monospace;
        font-size: 12px;
        font-weight: 700;
        text-transform: uppercase;
        cursor: pointer;
        transition: all 0.2s;
    }
    
    .btn-cancel:hover {
        border-color: #ff6b6b;
        color: #ff6b6b;
    }
    
    .empty-exercises {
        text-align: center;
        padding: 40px 20px;
        color: var(--text-dim);
    }
    
    .empty-exercises svg {
        width: 48px;
        height: 48px;
        margin: 0 auto 16px;
        opacity: 0.3;
    }
</style>
@endsection

@section('content')
@php
$exercises = App\Models\Exercise::orderBy('name')->get();
@endphp

<!-- Active Header -->
<div class="active-header">
    <div class="active-status">
        <span class="active-status-dot"></span>
        Active Workout
    </div>
</div>

<!-- Add Exercise -->
<div class="add-exercise-section">
    <div class="add-exercise-label">Add Exercise</div>
    <div class="exercise-select-row">
        <select id="exerciseSelect" class="form-input exercise-select">
            <option value="">Select exercise...</option>
            @foreach($exercises as $exercise)
                <option value="{{ $exercise->id }}">{{ $exercise->name }}</option>
            @endforeach
        </select>
        <button class="btn-add" onclick="addExercise()">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <line x1="12" y1="5" x2="12" y2="19"/>
                <line x1="5" y1="12" x2="19" y2="12"/>
            </svg>
        </button>
    </div>
</div>

<!-- Exercise Cards -->
<div id="exercisesContainer">
    @if($workout->sets->isEmpty())
        <div class="empty-exercises">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                <path d="M6 9H4.5a2.5 2.5 0 0 1 0-5H6"/>
                <path d="M18 9h1.5a2.5 2.5 0 0 0 0-5H18"/>
                <path d="M4 22h16"/>
                <path d="M10 14.66V17c0 .55-.47.98-.97 1.21C7.85 18.75 7 20.24 7 22"/>
                <path d="M14 14.66V17c0 .55.47.98.97 1.21C16.15 18.75 17 20.24 17 22"/>
                <path d="M18 2H6v7a6 6 0 0 0 12 0V2Z"/>
            </svg>
            <p>Add your first exercise to get started</p>
        </div>
    @else
        @php
            $grouped = $workout->sets->groupBy('exercise_id');
        @endphp
        @foreach($grouped as $exerciseId => $sets)
            @php $exercise = $sets->first()->exercise; @endphp
            <div class="exercise-card" data-exercise-id="{{ $exerciseId }}">
                <div class="exercise-card-header">
                    <div class="exercise-card-title">
                        <div class="exercise-card-icon">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M6 9H4.5a2.5 2.5 0 0 1 0-5H6"/>
                                <path d="M18 9h1.5a2.5 2.5 0 0 0 0-5H18"/>
                                <path d="M4 22h16"/>
                                <path d="M10 14.66V17c0 .55-.47.98-.97 1.21C7.85 18.75 7 20.24 7 22"/>
                                <path d="M14 14.66V17c0 .55.47.98.97 1.21C16.15 18.75 17 20.24 17 22"/>
                                <path d="M18 2H6v7a6 6 0 0 0 12 0V2Z"/>
                            </svg>
                        </div>
                        {{ $exercise->name }}
                    </div>
                    <span class="exercise-set-count">{{ $sets->count() }} sets</span>
                </div>
                <div class="exercise-card-body">
                    <div class="sets-grid">
                        @foreach($sets as $idx => $set)
                            <div class="set-box">
                                <button class="set-box-delete" onclick="deleteSet({{ $set->id }})">×</button>
                                <div class="set-box-number">Set {{ $idx + 1 }}</div>
                                <div class="set-box-value">{{ $set->weight ?? 0 }}×{{ $set->reps ?? 0 }}</div>
                            </div>
                        @endforeach
                    </div>
                    <div class="add-set-row">
                        <div class="add-set-input">
                            <input type="number" id="weight-{{ $exerciseId }}" placeholder="Weight (lbs)" step="0.5">
                        </div>
                        <div class="add-set-input">
                            <input type="number" id="reps-{{ $exerciseId }}" placeholder="Reps">
                        </div>
                        <button class="add-set-btn" onclick="addSet({{ $exerciseId }})">Add</button>
                    </div>
                </div>
            </div>
        @endforeach
    @endif
</div>

<!-- Notes -->
<div class="notes-section">
    <div class="notes-label">Workout Notes (Optional)</div>
    <textarea id="notesInput" class="notes-input" placeholder="How did it go? Any observations...">{{ $workout->notes }}</textarea>
</div>

<!-- Actions -->
<div class="action-buttons">
    <button class="btn-finish" onclick="finishWorkout()">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <polyline points="20 6 9 17 4 12"/>
        </svg>
        Finish Workout
    </button>
    <button class="btn-cancel" onclick="cancelWorkout()">Cancel Workout</button>
</div>
@endsection

@section('scripts')
<script>
let workoutId = {{ $workout->id }};

async function addExercise() {
    const exerciseId = document.getElementById('exerciseSelect').value;
    if (!exerciseId) return;
    
    await fetch(`/workouts/${workoutId}/sets`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
        },
        body: JSON.stringify({ exercise_id: exerciseId, weight: 0, reps: 0 })
    });
    
    location.reload();
}

async function addSet(exerciseId) {
    const weight = document.getElementById(`weight-${exerciseId}`).value;
    const reps = document.getElementById(`reps-${exerciseId}`).value;
    
    if (!weight || !reps) return;
    
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

async function finishWorkout() {
    const notes = document.getElementById('notesInput').value;
    
    if (notes) {
        await fetch(`/workouts/${workoutId}`, {
            method: 'PATCH',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            },
            body: JSON.stringify({ notes })
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
}

async function cancelWorkout() {
    if (!confirm('Cancel this workout? All data will be lost.')) return;
    
    await fetch(`/workouts/${workoutId}`, {
        method: 'DELETE',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
        }
    });
    
    window.location.href = '/dashboard';
}
</script>
@endsection
