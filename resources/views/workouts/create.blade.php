@extends('layouts.app')

@section('title', 'Log Workout')

@section('styles')
<style>
    /* Page Header */
    .page-header-center {
        text-align: center;
        margin-bottom: 32px;
        padding-top: 20px;
    }
    
    .header-logo {
        width: 80px;
        height: 48px;
        margin: 0 auto 16px;
    }
    
    .header-logo svg {
        width: 100%;
        height: 100%;
    }
    
    .page-title {
        font-size: 24px;
        font-weight: 700;
        letter-spacing: 2px;
        color: var(--text);
        margin-bottom: 8px;
    }
    
    .page-subtitle {
        font-size: 13px;
        color: var(--text-dim);
    }
    
    /* Continue Last Workout Card */
    .continue-card {
        background: #e8e8e8;
        border-radius: 4px;
        padding: 20px;
        margin-bottom: 24px;
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 16px;
    }
    
    .continue-card-info h3 {
        font-size: 13px;
        font-weight: 700;
        text-transform: uppercase;
        color: #1a1a1a;
        margin-bottom: 4px;
    }
    
    .continue-card-info p {
        font-size: 12px;
        color: #666;
        margin-bottom: 2px;
    }
    
    .continue-btn {
        padding: 12px 24px;
        background: #fff;
        border: 2px solid #1a1a1a;
        border-radius: 4px;
        font-family: 'Space Mono', monospace;
        font-size: 12px;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 1px;
        color: #1a1a1a;
        cursor: pointer;
        text-decoration: none;
        white-space: nowrap;
    }
    
    .continue-btn:hover {
        background: #1a1a1a;
        color: #fff;
    }
    
    /* Divider */
    .divider-text {
        text-align: center;
        font-size: 12px;
        color: var(--text-dim);
        margin-bottom: 20px;
    }
    
    /* Start Workout Button */
    .start-workout-container {
        background: #fff;
        border-radius: 4px;
        padding: 24px;
        text-align: center;
    }
    
    .start-workout-btn {
        width: 100%;
        padding: 18px;
        background: #1a1a1a;
        border: none;
        border-radius: 4px;
        color: #fff;
        font-family: 'Space Mono', monospace;
        font-size: 14px;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 2px;
        cursor: pointer;
        transition: all 0.2s;
    }
    
    .start-workout-btn:hover {
        background: #333;
        transform: translateY(-2px);
    }
    
    /* Active Workout Styles */
    .workout-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        margin-bottom: 20px;
        padding-bottom: 16px;
        border-bottom: 1px solid var(--border);
    }
    
    .workout-timer {
        font-size: 24px;
        font-weight: 700;
        color: var(--accent);
        font-family: 'Space Mono', monospace;
    }
    
    .finish-btn {
        padding: 10px 20px;
        background: var(--success);
        border: none;
        border-radius: 6px;
        color: #000;
        font-family: 'Space Mono', monospace;
        font-size: 12px;
        font-weight: 700;
        text-transform: uppercase;
        cursor: pointer;
    }
    
    .exercise-card {
        background: var(--surface);
        border: 1px solid var(--border);
        border-radius: 8px;
        padding: 16px;
        margin-bottom: 12px;
    }
    
    .exercise-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        margin-bottom: 12px;
    }
    
    .exercise-name {
        font-size: 14px;
        font-weight: 700;
        text-transform: uppercase;
    }
    
    .exercise-actions {
        display: flex;
        gap: 8px;
    }
    
    .icon-btn-small {
        width: 32px;
        height: 32px;
        background: var(--bg);
        border: 1px solid var(--border);
        border-radius: 6px;
        color: var(--text);
        cursor: pointer;
        display: flex;
        align-items: center;
        justify-content: center;
    }
    
    .sets-table {
        width: 100%;
        margin-bottom: 12px;
    }
    
    .sets-table-header {
        display: grid;
        grid-template-columns: 40px 1fr 1fr 40px;
        gap: 8px;
        padding: 8px 0;
        font-size: 10px;
        color: var(--text-dim);
        text-transform: uppercase;
        border-bottom: 1px solid var(--border);
    }
    
    .set-row {
        display: grid;
        grid-template-columns: 40px 1fr 1fr 40px;
        gap: 8px;
        padding: 8px 0;
        align-items: center;
        border-bottom: 1px solid var(--border);
    }
    
    .set-row:last-child {
        border-bottom: none;
    }
    
    .set-number {
        font-size: 12px;
        color: var(--text-dim);
    }
    
    .set-input {
        width: 100%;
        padding: 8px;
        background: var(--bg);
        border: 1px solid var(--border);
        border-radius: 4px;
        font-family: 'Space Mono', monospace;
        font-size: 13px;
        color: var(--text);
        text-align: center;
    }
    
    .add-set-btn {
        width: 100%;
        padding: 10px;
        background: var(--bg);
        border: 1px dashed var(--border);
        border-radius: 6px;
        color: var(--text-dim);
        font-family: 'Space Mono', monospace;
        font-size: 11px;
        cursor: pointer;
    }
    
    .add-exercise-btn {
        width: 100%;
        padding: 16px;
        background: var(--surface);
        border: 2px dashed var(--border);
        border-radius: 8px;
        color: var(--text);
        font-family: 'Space Mono', monospace;
        font-size: 12px;
        font-weight: 700;
        text-transform: uppercase;
        cursor: pointer;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
    }
</style>
@endsection

@section('content')
@php
$activeWorkout = Auth::user()->workouts()->active()->first();
@endphp

@if($activeWorkout)
    <!-- Active Workout View -->
    <div class="workout-header">
        <div>
            <div style="font-size: 11px; color: var(--text-dim); text-transform: uppercase; letter-spacing: 1px; margin-bottom: 4px;">Workout #{{ $activeWorkout->id }}</div>
            <div class="workout-timer" id="workoutTimer">00:00</div>
        </div>
        <button class="finish-btn" onclick="finishWorkout()">Finish</button>
    </div>
    
    <div id="exercisesList">
        @php
        $sets = $activeWorkout->sets()->with('exercise')->get()->groupBy('exercise_id');
        @endphp
        
        @foreach($sets as $exerciseId => $exerciseSets)
            @php $exercise = $exerciseSets->first()->exercise; @endphp
            <div class="exercise-card" data-exercise-id="{{ $exerciseId }}">
                <div class="exercise-header">
                    <span class="exercise-name">{{ $exercise->name }}</span>
                    <div class="exercise-actions">
                        <button class="icon-btn-small" onclick="addSet({{ $exerciseId }})">+</button>
                        <button class="icon-btn-small" onclick="removeExercise({{ $exerciseId }})" style="color: #ff6b6b;">×</button>
                    </div>
                </div>
                <div class="sets-table">
                    <div class="sets-table-header">
                        <span>Set</span>
                        <span>Reps</span>
                        <span>Weight</span>
                        <span></span>
                    </div>
                    @foreach($exerciseSets as $idx => $set)
                        <div class="set-row" data-set-id="{{ $set->id }}">
                            <span class="set-number">{{ $idx + 1 }}</span>
                            <input type="number" class="set-input" value="{{ $set->reps }}" placeholder="0" onchange="updateSet({{ $set->id }}, 'reps', this.value)">
                            <input type="number" class="set-input" value="{{ $set->weight }}" placeholder="0" step="0.5" onchange="updateSet({{ $set->id }}, 'weight', this.value)">
                            <button class="icon-btn-small" onclick="deleteSet({{ $set->id }})" style="width: 28px; height: 28px;">×</button>
                        </div>
                    @endforeach
                </div>
            </div>
        @endforeach
    </div>
    
    <button class="add-exercise-btn" onclick="showAddExerciseModal()">
        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <line x1="12" y1="5" x2="12" y2="19"/>
            <line x1="5" y1="12" x2="19" y2="12"/>
        </svg>
        Add Exercise
    </button>

@else
    <!-- No Active Workout - Show Start Screen -->
    <div class="page-header-center">
        <div class="header-logo">
            <svg viewBox="0 0 100 60" fill="none" xmlns="http://www.w3.org/2000/svg">
                <rect x="20" y="26" width="60" height="8" fill="#e0e0e0"/>
                <rect x="4" y="14" width="8" height="32" fill="#c0c0c0"/>
                <rect x="14" y="8" width="6" height="44" fill="#d0d0d0"/>
                <rect x="22" y="18" width="6" height="24" fill="#b0b0b0"/>
                <rect x="88" y="14" width="8" height="32" fill="#c0c0c0"/>
                <rect x="80" y="8" width="6" height="44" fill="#d0d0d0"/>
                <rect x="72" y="18" width="6" height="24" fill="#b0b0b0"/>
                <rect x="42" y="24" width="16" height="12" fill="#a0a0a0"/>
            </svg>
        </div>
        <h1 class="page-title">LOG WORKOUT</h1>
        <p class="page-subtitle">Let's begin. Create a new workout or pick up from last time.</p>
    </div>
    
    @php
    $lastWorkout = Auth::user()->workouts()
        ->completed()
        ->with('sets.exercise')
        ->latest('ended_at')
        ->first();
    @endphp
    
    @if($lastWorkout)
        <!-- Continue Last Workout -->
        <div class="continue-card">
            <div class="continue-card-info">
                <h3>CONTINUE LAST WORKOUT</h3>
                <p>{{ $lastWorkout->sets->first()->exercise->name ?? 'Workout' }} Routine (from {{ date('M j', $lastWorkout->ended_at / 1000) }})</p>
                <p>{{ $lastWorkout->sets->count() }} Sets | {{ number_format($lastWorkout->volume) }} lbs</p>
            </div>
            <a href="/workouts/{{ $lastWorkout->id }}" class="continue-btn">CONTINUE</a>
        </div>
    @endif
    
    <p class="divider-text">or create a custom one below</p>
    
    <!-- Start New Workout -->
    <div class="start-workout-container">
        <button class="start-workout-btn" onclick="startWorkout()">START WORKOUT</button>
    </div>
@endif
@endsection

@section('scripts')
<script>
let workoutStartTime = {{ $activeWorkout ? $activeWorkout->started_at : 'null' }};

// Timer for active workout
if (workoutStartTime) {
    function updateTimer() {
        const now = Date.now();
        const diff = Math.floor((now - workoutStartTime) / 1000);
        const minutes = Math.floor(diff / 60).toString().padStart(2, '0');
        const seconds = (diff % 60).toString().padStart(2, '0');
        document.getElementById('workoutTimer').textContent = `${minutes}:${seconds}`;
    }
    updateTimer();
    setInterval(updateTimer, 1000);
}

async function startWorkout() {
    const res = await fetch('/workouts', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
        }
    });
    
    const data = await res.json();
    if (data.id) {
        window.location.reload();
    }
}

async function finishWorkout() {
    const activeWorkoutId = {{ $activeWorkout ? $activeWorkout->id : 'null' }};
    if (!activeWorkoutId) return;
    
    await fetch(`/workouts/${activeWorkoutId}/finish`, {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
        }
    });
    
    window.location.href = '/workouts';
}

async function updateSet(setId, field, value) {
    await fetch(`/sets/${setId}`, {
        method: 'PATCH',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
        },
        body: JSON.stringify({ [field]: parseFloat(value) || 0 })
    });
}

async function addSet(exerciseId) {
    const activeWorkoutId = {{ $activeWorkout ? $activeWorkout->id : 'null' }};
    if (!activeWorkoutId) return;
    
    await fetch('/sets', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
        },
        body: JSON.stringify({
            workout_id: activeWorkoutId,
            exercise_id: exerciseId,
            reps: 0,
            weight: 0
        })
    });
    
    window.location.reload();
}

async function deleteSet(setId) {
    await fetch(`/sets/${setId}`, {
        method: 'DELETE',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
        }
    });
    
    window.location.reload();
}

async function removeExercise(exerciseId) {
    if (!confirm('Remove this exercise and all its sets?')) return;
    
    const activeWorkoutId = {{ $activeWorkout ? $activeWorkout->id : 'null' }};
    if (!activeWorkoutId) return;
    
    await fetch(`/workouts/${activeWorkoutId}/exercises/${exerciseId}`, {
        method: 'DELETE',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
        }
    });
    
    window.location.reload();
}

function showAddExerciseModal() {
    const exerciseName = prompt('Enter exercise name:');
    if (!exerciseName) return;
    
    // First find or create exercise
    fetch('/exercises/find-or-create', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
        },
        body: JSON.stringify({ name: exerciseName })
    })
    .then(res => res.json())
    .then(data => {
        if (data.id) {
            // Add exercise to workout
            const activeWorkoutId = {{ $activeWorkout ? $activeWorkout->id : 'null' }};
            return fetch('/sets', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                },
                body: JSON.stringify({
                    workout_id: activeWorkoutId,
                    exercise_id: data.id,
                    reps: 0,
                    weight: 0
                })
            });
        }
    })
    .then(() => window.location.reload());
}
</script>
@endsection
