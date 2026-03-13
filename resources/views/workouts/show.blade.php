@extends('layouts.app')

@section('title', 'Workout Details')

@section('styles')
<style>
    /* Navigation Bar */
    .detail-nav {
        display: flex;
        align-items: center;
        justify-content: space-between;
        margin-bottom: 20px;
        padding-bottom: 16px;
        border-bottom: 1px solid var(--border);
    }
    
    .back-btn {
        display: flex;
        align-items: center;
        gap: 8px;
        padding: 10px 14px;
        background: var(--surface);
        border: 1px solid var(--border);
        border-radius: 8px;
        font-family: 'Space Mono', monospace;
        font-size: 12px;
        color: var(--text);
        text-decoration: none;
        cursor: pointer;
        transition: all 0.2s;
    }
    
    .back-btn:hover {
        border-color: var(--accent);
        color: var(--accent);
    }
    
    .back-btn svg {
        width: 16px;
        height: 16px;
    }
    
    .nav-actions {
        display: flex;
        gap: 8px;
    }
    
    .nav-action-btn {
        display: flex;
        align-items: center;
        justify-content: center;
        width: 40px;
        height: 40px;
        background: var(--surface);
        border: 1px solid var(--border);
        border-radius: 8px;
        color: var(--text);
        cursor: pointer;
        transition: all 0.2s;
    }
    
    .nav-action-btn:hover {
        border-color: var(--accent);
        color: var(--accent);
    }
    
    .nav-action-btn svg {
        width: 18px;
        height: 18px;
    }
    
    /* Workout Header Card */
    .workout-header-card {
        background: var(--surface);
        border: 1px solid var(--border);
        border-radius: 12px;
        padding: 20px;
        margin-bottom: 20px;
    }
    
    .workout-date-display {
        font-size: 13px;
        color: var(--text-dim);
        text-transform: uppercase;
        letter-spacing: 1px;
        margin-bottom: 4px;
    }
    
    .workout-title-display {
        font-size: 22px;
        font-weight: 700;
        margin-bottom: 8px;
    }
    
    .workout-duration {
        display: flex;
        align-items: center;
        gap: 6px;
        font-size: 13px;
        color: var(--text-dim);
    }
    
    .workout-duration svg {
        width: 14px;
        height: 14px;
    }
    
    /* Stats Grid */
    .stats-grid {
        display: grid;
        grid-template-columns: repeat(2, 1fr);
        gap: 12px;
        margin-top: 20px;
    }
    
    .stat-box {
        background: var(--bg);
        border-radius: 10px;
        padding: 16px;
        text-align: center;
    }
    
    .stat-box.primary {
        grid-column: 1 / -1;
        background: linear-gradient(135deg, var(--accent) 0%, var(--accent-hover) 100%);
    }
    
    .stat-box.primary .stat-box-value,
    .stat-box.primary .stat-box-label {
        color: #fff;
    }
    
    .stat-box-value {
        font-size: 24px;
        font-weight: 700;
        margin-bottom: 4px;
    }
    
    .stat-box-label {
        font-size: 10px;
        color: var(--text-dim);
        text-transform: uppercase;
        letter-spacing: 1px;
    }
    
    /* Section Title */
    .section-title {
        display: flex;
        align-items: center;
        gap: 8px;
        font-size: 12px;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 1px;
        color: var(--text-dim);
        margin-bottom: 16px;
    }
    
    .section-title svg {
        width: 16px;
        height: 16px;
    }
    
    /* Exercise Groups */
    .exercise-group {
        background: var(--surface);
        border: 1px solid var(--border);
        border-radius: 12px;
        margin-bottom: 16px;
        overflow: hidden;
    }
    
    .exercise-group-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 16px;
        background: var(--surface-hover);
        cursor: pointer;
        transition: background 0.2s;
    }
    
    .exercise-group-header:hover {
        background: var(--bg);
    }
    
    .exercise-group-title {
        display: flex;
        align-items: center;
        gap: 10px;
    }
    
    .exercise-group-title h3 {
        font-size: 14px;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }
    
    .exercise-group-icon {
        width: 28px;
        height: 28px;
        background: var(--bg);
        border-radius: 6px;
        display: flex;
        align-items: center;
        justify-content: center;
    }
    
    .exercise-group-icon svg {
        width: 16px;
        height: 16px;
        color: var(--accent);
    }
    
    .exercise-group-meta {
        font-size: 11px;
        color: var(--text-dim);
    }
    
    .exercise-group-expand {
        width: 20px;
        height: 20px;
        color: var(--text-dim);
        transition: transform 0.2s;
    }
    
    .exercise-group.expanded .exercise-group-expand {
        transform: rotate(180deg);
    }
    
    .exercise-group-body {
        display: none;
        padding: 0 16px 16px;
    }
    
    .exercise-group.expanded .exercise-group-body {
        display: block;
    }
    
    /* Sets Table */
    .sets-table {
        width: 100%;
        margin-top: 12px;
    }
    
    .sets-table-header {
        display: grid;
        grid-template-columns: 50px 1fr 1fr 1fr;
        gap: 8px;
        padding: 8px 0;
        border-bottom: 1px solid var(--border);
        font-size: 10px;
        color: var(--text-dim);
        text-transform: uppercase;
        letter-spacing: 1px;
    }
    
    .sets-table-row {
        display: grid;
        grid-template-columns: 50px 1fr 1fr 1fr;
        gap: 8px;
        padding: 10px 0;
        border-bottom: 1px solid var(--border);
        font-size: 13px;
    }
    
    .sets-table-row:last-child {
        border-bottom: none;
    }
    
    .set-number {
        display: flex;
        align-items: center;
        justify-content: center;
        width: 28px;
        height: 28px;
        background: var(--bg);
        border-radius: 6px;
        font-size: 11px;
        font-weight: 700;
    }
    
    .set-value {
        display: flex;
        align-items: center;
        font-weight: 600;
    }
    
    .set-label {
        font-size: 10px;
        color: var(--text-dim);
        margin-left: 4px;
        text-transform: uppercase;
    }
    
    /* Notes Section */
    .notes-section {
        background: var(--surface);
        border: 1px solid var(--border);
        border-radius: 12px;
        padding: 16px;
        margin-top: 20px;
    }
    
    .notes-label {
        display: flex;
        align-items: center;
        gap: 6px;
        font-size: 11px;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 1px;
        color: var(--text-dim);
        margin-bottom: 8px;
    }
    
    .notes-label svg {
        width: 14px;
        height: 14px;
    }
    
    .notes-content {
        font-size: 14px;
        line-height: 1.6;
        color: var(--text);
    }
    
    .notes-empty {
        font-size: 13px;
        color: var(--text-dim);
        font-style: italic;
    }
    
    /* Action Buttons */
    .detail-actions {
        display: flex;
        gap: 12px;
        margin-top: 24px;
    }
    
    .detail-action-btn {
        flex: 1;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
        padding: 14px;
        background: var(--surface);
        border: 1px solid var(--border);
        border-radius: 10px;
        font-family: 'Space Mono', monospace;
        font-size: 12px;
        font-weight: 700;
        text-transform: uppercase;
        color: var(--text);
        cursor: pointer;
        text-decoration: none;
        transition: all 0.2s;
    }
    
    .detail-action-btn:hover {
        border-color: var(--accent);
        color: var(--accent);
    }
    
    .detail-action-btn.delete:hover {
        border-color: #ff6b6b;
        color: #ff6b6b;
    }
    
    .detail-action-btn svg {
        width: 16px;
        height: 16px;
    }
</style>
@endsection

@section('content')
@php
$sets = $workout->sets()->with('exercise')->orderBy('completed_at')->get();
$volume = $sets->sum(fn($s) => ($s->weight ?? 0) * ($s->reps ?? 0));
$grouped = $sets->groupBy('exercise_id');

$timestamp = $workout->started_at / 1000;
$dayName = date('l', $timestamp);
$dateStr = date('F j, Y', $timestamp);

$duration = 0;
if ($workout->ended_at && $workout->started_at) {
    $duration = round(($workout->ended_at - $workout->started_at) / 60000);
}
@endphp

<!-- Navigation -->
<div class="detail-nav">
    <a href="/workouts" class="back-btn">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <path d="M19 12H5M12 19l-7-7 7-7"/>
        </svg>
        Back to History
    </a>
    <div class="nav-actions">
        <button class="nav-action-btn" onclick="shareWorkout()" title="Share">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M4 12v8a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2v-8"/>
                <polyline points="16 6 12 2 8 6"/>
                <line x1="12" y1="2" x2="12" y2="15"/>
            </svg>
        </button>
        <button class="nav-action-btn delete" onclick="deleteWorkout()" title="Delete">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <polyline points="3 6 5 6 21 6"/>
                <path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/>
            </svg>
        </button>
    </div>
</div>

<!-- Workout Header -->
<div class="workout-header-card">
    <div class="workout-date-display">{{ $dayName }}</div>
    <h1 class="workout-title-display">{{ $dateStr }}</h1>
    @if($duration > 0)
        <div class="workout-duration">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <circle cx="12" cy="12" r="10"/>
                <polyline points="12 6 12 12 16 14"/>
            </svg>
            {{ $duration }} minutes
        </div>
    @endif
    
    <!-- Stats -->
    <div class="stats-grid">
        <div class="stat-box primary">
            <div class="stat-box-value">{{ number_format($volume) }}</div>
            <div class="stat-box-label">Total Volume (lbs)</div>
        </div>
        <div class="stat-box">
            <div class="stat-box-value">{{ $sets->count() }}</div>
            <div class="stat-box-label">Total Sets</div>
        </div>
        <div class="stat-box">
            <div class="stat-box-value">{{ $grouped->count() }}</div>
            <div class="stat-box-label">Exercises</div>
        </div>
    </div>
</div>

<!-- Exercises -->
<div class="section-title">
    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
        <path d="M6 9H4.5a2.5 2.5 0 0 1 0-5H6"/>
        <path d="M18 9h1.5a2.5 2.5 0 0 0 0-5H18"/>
        <path d="M4 22h16"/>
        <path d="M10 14.66V17c0 .55-.47.98-.97 1.21C7.85 18.75 7 20.24 7 22"/>
        <path d="M14 14.66V17c0 .55.47.98.97 1.21C16.15 18.75 17 20.24 17 22"/>
        <path d="M18 2H6v7a6 6 0 0 0 12 0V2Z"/>
    </svg>
    Exercises
</div>

@foreach($grouped as $exerciseId => $exSets)
    @php
        $exercise = $exSets->first()->exercise;
        $exerciseVolume = $exSets->sum(fn($s) => ($s->weight ?? 0) * ($s->reps ?? 0));
    @endphp
    <div class="exercise-group expanded">
        <div class="exercise-group-header" onclick="toggleExerciseGroup(this)">
            <div class="exercise-group-title">
                <div class="exercise-group-icon">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M6 9H4.5a2.5 2.5 0 0 1 0-5H6"/>
                        <path d="M18 9h1.5a2.5 2.5 0 0 0 0-5H18"/>
                        <path d="M4 22h16"/>
                        <path d="M10 14.66V17c0 .55-.47.98-.97 1.21C7.85 18.75 7 20.24 7 22"/>
                        <path d="M14 14.66V17c0 .55.47.98.97 1.21C16.15 18.75 17 20.24 17 22"/>
                        <path d="M18 2H6v7a6 6 0 0 0 12 0V2Z"/>
                    </svg>
                </div>
                <h3>{{ $exercise->name }}</h3>
            </div>
            <div style="display: flex; align-items: center; gap: 12px;">
                <span class="exercise-group-meta">{{ number_format($exerciseVolume) }} lbs</span>
                <svg class="exercise-group-expand" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <polyline points="6 9 12 15 18 9"/>
                </svg>
            </div>
        </div>
        <div class="exercise-group-body">
            <div class="sets-table">
                <div class="sets-table-header">
                    <span>Set</span>
                    <span>Weight</span>
                    <span>Reps</span>
                    <span>Volume</span>
                </div>
                @foreach($exSets as $idx => $set)
                    <div class="sets-table-row">
                        <span class="set-number">{{ $idx + 1 }}</span>
                        <span class="set-value">{{ $set->weight ?? 0 }} <span class="set-label">lbs</span></span>
                        <span class="set-value">{{ $set->reps ?? 0 }} <span class="set-label">reps</span></span>
                        <span class="set-value">{{ number_format(($set->weight ?? 0) * ($set->reps ?? 0)) }} <span class="set-label">lbs</span></span>
                    </div>
                @endforeach
            </div>
        </div>
    </div>
@endforeach

<!-- Notes -->
@if($workout->notes)
    <div class="notes-section">
        <div class="notes-label">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/>
                <polyline points="14 2 14 8 20 8"/>
                <line x1="16" y1="13" x2="8" y2="13"/>
                <line x1="16" y1="17" x2="8" y2="17"/>
                <polyline points="10 9 9 9 8 9"/>
            </svg>
            Workout Notes
        </div>
        <div class="notes-content">{{ $workout->notes }}</div>
    </div>
@endif

<!-- Actions -->
<div class="detail-actions">
    <a href="/workouts/create" class="detail-action-btn">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <polygon points="5 3 19 12 5 21 5 3"/>
        </svg>
        Repeat Workout
    </a>
    <button class="detail-action-btn delete" onclick="deleteWorkout()">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <polyline points="3 6 5 6 21 6"/>
            <path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/>
        </svg>
        Delete
    </button>
</div>
@endsection

@section('scripts')
<script>
function toggleExerciseGroup(header) {
    const group = header.closest('.exercise-group');
    group.classList.toggle('expanded');
}

function shareWorkout() {
    if (navigator.share) {
        navigator.share({
            title: 'My Workout',
            text: 'Check out my workout on Workout Tracker!'
        });
    } else {
        // Fallback - copy to clipboard
        alert('Share feature coming soon!');
    }
}

function deleteWorkout() {
    if (!confirm('Delete this workout? This cannot be undone.')) return;
    
    fetch(`/workouts/{{ $workout->id }}`, {
        method: 'DELETE',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
        }
    }).then(() => {
        window.location.href = '/workouts';
    });
}
</script>
@endsection
