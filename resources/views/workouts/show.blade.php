@extends('layouts.app')

@section('title', 'Workout Details')

@section('styles')
<style>
    /* Back Button */
    .back-link {
        display: flex;
        align-items: center;
        gap: 8px;
        margin-bottom: 20px;
        color: var(--text);
        text-decoration: none;
        font-size: 12px;
        text-transform: uppercase;
        letter-spacing: 1px;
    }
    
    .back-link svg {
        width: 16px;
        height: 16px;
    }
    
    /* Workout Header Card - White */
    .workout-header-card {
        background: #fff;
        border-radius: 4px;
        padding: 20px;
        margin-bottom: 20px;
    }
    
    .workout-date-display {
        font-size: 16px;
        font-weight: 700;
        color: #1a1a1a;
        margin-bottom: 4px;
    }
    
    .workout-duration {
        font-size: 12px;
        color: #666;
        margin-bottom: 16px;
    }
    
    /* Stats Grid */
    .stats-grid {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 12px;
        border-top: 1px solid #e0e0e0;
        padding-top: 16px;
    }
    
    .stat-box {
        text-align: center;
    }
    
    .stat-box-value {
        font-size: 28px;
        font-weight: 700;
        color: #1a1a1a;
    }
    
    .stat-box-label {
        font-size: 10px;
        color: #666;
        text-transform: uppercase;
        letter-spacing: 1px;
    }
    
    /* Section Title */
    .section-title {
        font-size: 12px;
        color: var(--text-dim);
        text-transform: uppercase;
        letter-spacing: 2px;
        margin-bottom: 16px;
    }
    
    /* Exercise Card */
    .exercise-card {
        background: #fff;
        border-radius: 4px;
        padding: 16px;
        margin-bottom: 12px;
    }
    
    .exercise-header {
        display: flex;
        align-items: center;
        gap: 8px;
        margin-bottom: 12px;
        font-size: 14px;
        font-weight: 700;
        color: #1a1a1a;
        text-transform: uppercase;
    }
    
    .exercise-header svg {
        width: 18px;
        height: 18px;
        color: var(--accent);
    }
    
    /* Sets Grid */
    .sets-grid {
        display: grid;
        grid-template-columns: repeat(2, 1fr);
        gap: 8px;
    }
    
    .set-item {
        display: flex;
        align-items: center;
        gap: 8px;
        padding: 10px;
        background: #f5f5f5;
        border-radius: 4px;
        font-size: 12px;
    }
    
    .set-number {
        font-weight: 700;
        color: #666;
        min-width: 40px;
    }
    
    .set-details {
        color: #1a1a1a;
    }
    
    /* Notes Section */
    .notes-section {
        background: #fff;
        border-radius: 4px;
        padding: 16px;
        margin-top: 20px;
    }
    
    .notes-label {
        font-size: 11px;
        color: #666;
        text-transform: uppercase;
        letter-spacing: 1px;
        margin-bottom: 8px;
    }
    
    .notes-content {
        font-size: 13px;
        color: #1a1a1a;
        line-height: 1.5;
    }
    
    /* Action Buttons */
    .detail-actions {
        display: flex;
        gap: 12px;
        margin-top: 24px;
    }
    
    .detail-action-btn {
        flex: 1;
        padding: 14px;
        background: var(--surface);
        border: 1px solid var(--border);
        border-radius: 6px;
        font-family: 'Space Mono', monospace;
        font-size: 12px;
        font-weight: 700;
        text-transform: uppercase;
        color: var(--text);
        cursor: pointer;
        text-decoration: none;
        text-align: center;
    }
    
    .detail-action-btn.delete {
        color: #ff6b6b;
        border-color: #ff6b6b;
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
@endphp

<!-- Back Button -->
<a href="/workouts" class="back-link">
    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
        <path d="M19 12H5M12 19l-7-7 7-7"/>
    </svg>
    BACK
</a>

<!-- Workout Header -->
<div class="workout-header-card">
    <div class="workout-date-display">{{ $dayName }}, {{ date('M j, Y', $timestamp) }}</div>
    <div class="workout-duration">Duration: {{ floor($workout->duration / 60000) }} minutes</div>
    
    <div class="stats-grid">
        <div class="stat-box">
            <div class="stat-box-value">{{ $sets->count() }}</div>
            <div class="stat-box-label">SETS</div>
        </div>
        <div class="stat-box">
            <div class="stat-box-value">{{ number_format($volume) }}</div>
            <div class="stat-box-label">lbs VOLUME</div>
        </div>
    </div>
</div>

<!-- Exercises -->
<p class="section-title">EXERCISES</p>

@foreach($grouped as $exerciseId => $exSets)
    @php
        $exercise = $exSets->first()->exercise;
    @endphp
    <div class="exercise-card">
        <div class="exercise-header">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M6 9H4.5a2.5 2.5 0 0 1 0-5H6"/>
                <path d="M18 9h1.5a2.5 2.5 0 0 0 0-5H18"/>
                <path d="M4 22h16"/>
                <path d="M10 14.66V17c0 .55-.47.98-.97 1.21C7.85 18.75 7 20.24 7 22"/>
                <path d="M14 14.66V17c0 .55.47.98.97 1.21C16.15 18.75 17 20.24 17 22"/>
                <path d="M18 2H6v7a6 6 0 0 0 12 0V2Z"/>
            </svg>
            {{ $exercise->name }}
        </div>
        <div class="sets-grid">
            @foreach($exSets as $idx => $set)
                <div class="set-item">
                    <span class="set-number">Set {{ $idx + 1 }}:</span>
                    <span class="set-details">{{ $set->reps }} reps @ {{ $set->weight }} lbs</span>
                </div>
            @endforeach
        </div>
    </div>
@endforeach

<!-- Notes -->
@if($workout->notes)
    <div class="notes-section">
        <div class="notes-label">NOTES</div>
        <div class="notes-content">{{ $workout->notes }}</div>
    </div>
@endif

<!-- Actions -->
<div class="detail-actions">
    <a href="/workouts/create" class="detail-action-btn">Repeat Workout</a>
    <button class="detail-action-btn delete" onclick="deleteWorkout()">Delete</button>
</div>
@endsection

@section('scripts')
<script>
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
