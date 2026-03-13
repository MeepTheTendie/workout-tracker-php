@extends('layouts.app')

@section('title', 'Log Workout')

@section('styles')
<style>
    .page-header-log {
        display: flex;
        align-items: center;
        gap: 12px;
        margin-bottom: 20px;
        padding-bottom: 16px;
        border-bottom: 1px solid var(--border);
    }
    
    .page-header-log svg {
        width: 32px;
        height: 20px;
        fill: var(--accent);
    }
    
    .page-title-group h1 {
        font-size: 18px;
        font-weight: 700;
        letter-spacing: 1px;
        margin-bottom: 2px;
    }
    
    .page-title-group p {
        font-size: 11px;
        color: var(--text-dim);
        text-transform: uppercase;
        letter-spacing: 1px;
    }
    
    /* Continue Previous Workout Card */
    .continue-card {
        background: linear-gradient(135deg, var(--surface) 0%, rgba(255,107,53,0.1) 100%);
        border: 1px solid var(--accent);
        border-radius: 12px;
        padding: 16px;
        margin-bottom: 16px;
    }
    
    .continue-card-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        margin-bottom: 12px;
    }
    
    .continue-card-label {
        display: flex;
        align-items: center;
        gap: 6px;
        font-size: 11px;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 1px;
        color: var(--accent);
    }
    
    .continue-card-label svg {
        width: 14px;
        height: 14px;
    }
    
    .continue-card-time {
        font-size: 11px;
        color: var(--text-dim);
    }
    
    .continue-card-workout {
        font-size: 14px;
        font-weight: 700;
        margin-bottom: 4px;
    }
    
    .continue-card-meta {
        font-size: 12px;
        color: var(--text-dim);
        margin-bottom: 12px;
    }
    
    .continue-card-btn {
        width: 100%;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
        padding: 12px;
        background: var(--accent);
        border: none;
        border-radius: 8px;
        color: #fff;
        font-family: 'Space Mono', monospace;
        font-size: 12px;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 1px;
        cursor: pointer;
        text-decoration: none;
        transition: all 0.2s;
    }
    
    .continue-card-btn:hover {
        background: var(--accent-hover);
    }
    
    .continue-card-btn svg {
        width: 16px;
        height: 16px;
    }
    
    /* Start New Workout Section */
    .start-section {
        text-align: center;
        padding: 40px 20px;
    }
    
    .start-icon {
        width: 64px;
        height: 64px;
        margin: 0 auto 20px;
        background: var(--surface);
        border: 2px solid var(--border);
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
    }
    
    .start-icon svg {
        width: 28px;
        height: 28px;
        color: var(--accent);
    }
    
    .start-title {
        font-size: 16px;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 1px;
        margin-bottom: 8px;
    }
    
    .start-subtitle {
        font-size: 12px;
        color: var(--text-dim);
        margin-bottom: 24px;
    }
    
    .start-btn {
        display: inline-flex;
        align-items: center;
        gap: 10px;
        padding: 16px 32px;
        background: #fff;
        border: none;
        border-radius: 10px;
        color: #1a1a1a;
        font-family: 'Space Mono', monospace;
        font-size: 14px;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 1px;
        cursor: pointer;
        transition: all 0.2s;
    }
    
    .start-btn:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 20px rgba(255,255,255,0.1);
    }
    
    .start-btn svg {
        width: 18px;
        height: 18px;
    }
    
    /* Quick Routine Access */
    .quick-routines {
        margin-top: 32px;
        padding-top: 24px;
        border-top: 1px solid var(--border);
    }
    
    .quick-routines-title {
        font-size: 11px;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 1px;
        color: var(--text-dim);
        margin-bottom: 12px;
        text-align: center;
    }
    
    .quick-routine-chips {
        display: flex;
        flex-wrap: wrap;
        justify-content: center;
        gap: 8px;
    }
    
    .quick-routine-chip {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 8px 14px;
        background: var(--surface);
        border: 1px solid var(--border);
        border-radius: 20px;
        font-size: 11px;
        color: var(--text);
        cursor: pointer;
        text-decoration: none;
        transition: all 0.2s;
    }
    
    .quick-routine-chip:hover {
        background: var(--surface-hover);
        border-color: var(--accent);
    }
    
    .quick-routine-chip svg {
        width: 12px;
        height: 12px;
        color: var(--accent);
    }
</style>
@endsection

@section('content')
@php
$activeWorkout = Auth::user()->workouts()->active()->first();
$recentRoutines = Auth::user()->routines()->with('exercises')->take(3)->get();
@endphp

<!-- Header with Branding -->
<div class="page-header-log">
    <svg viewBox="0 0 80 48" fill="none" xmlns="http://www.w3.org/2000/svg">
        <rect x="12" y="22" width="56" height="4"/>
        <rect x="4" y="14" width="4" height="20"/>
        <rect x="10" y="10" width="4" height="28"/>
        <rect x="16" y="16" width="4" height="16"/>
        <rect x="72" y="14" width="4" height="20"/>
        <rect x="66" y="10" width="4" height="28"/>
        <rect x="60" y="16" width="4" height="16"/>
    </svg>
    <div class="page-title-group">
        <h1>LOG WORKOUT</h1>
        <p>Track your lifts. See your progress.</p>
    </div>
</div>

@if($activeWorkout)
    <!-- Show Active Workout if one exists -->
    <script>
        window.location.href = '/workouts/{{ $activeWorkout->id }}/edit';
    </script>
@else
    @php
    // Check for very recent workout (within last hour) to suggest continuing
    $recentWorkout = Auth::user()->workouts()
        ->completed()
        ->where('ended_at', '>=', (time() - 3600) * 1000)
        ->with('sets.exercise')
        ->first();
    @endphp
    
    @if($recentWorkout)
        <!-- Continue Previous Workout Card -->
        <div class="continue-card">
            <div class="continue-card-header">
                <span class="continue-card-label">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <polyline points="23 4 23 10 17 10"/>
                        <path d="M20.49 15a9 9 0 1 1-2.12-9.36L23 10"/>
                    </svg>
                    Just Finished
                </span>
            </div>
            <div class="continue-card-workout">Workout #{{ $recentWorkout->id }}</div>
            <div class="continue-card-meta">
                {{ $recentWorkout->sets->count() }} sets • {{ number_format($recentWorkout->volume / 1000, 1) }}k lbs
            </div>
            <a href="/workouts/{{ $recentWorkout->id }}" class="continue-card-btn">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/>
                    <circle cx="12" cy="12" r="3"/>
                </svg>
                View Details
            </a>
        </div>
    @endif
    
    <!-- Start New Workout -->
    <div class="start-section">
        <div class="start-icon">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <circle cx="12" cy="12" r="10"/>
                <line x1="12" y1="8" x2="12" y2="16"/>
                <line x1="8" y1="12" x2="16" y2="12"/>
            </svg>
        </div>
        <h2 class="start-title">Start Fresh</h2>
        <p class="start-subtitle">Begin a new workout session</p>
        <button class="start-btn" onclick="startWorkout()">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <polygon points="5 3 19 12 5 21 5 3"/>
            </svg>
            Start Workout
        </button>
    </div>
    
    @if($recentRoutines->isNotEmpty())
        <!-- Quick Routine Access -->
        <div class="quick-routines">
            <p class="quick-routines-title">Or start from a routine</p>
            <div class="quick-routine-chips">
                @foreach($recentRoutines as $routine)
                    <button class="quick-routine-chip" onclick="startFromRoutine({{ $routine->id }})">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <rect x="3" y="4" width="18" height="18" rx="2" ry="2"/>
                            <line x1="16" y1="2" x2="16" y2="6"/>
                            <line x1="8" y1="2" x2="8" y2="6"/>
                            <line x1="3" y1="10" x2="21" y2="10"/>
                        </svg>
                        {{ $routine->name }}
                    </button>
                @endforeach
            </div>
        </div>
    @endif
@endif
@endsection

@section('scripts')
<script>
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
        window.location.href = `/workouts/${data.id}/edit`;
    }
}

async function startFromRoutine(routineId) {
    const res = await fetch('/workouts', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
        },
        body: JSON.stringify({ routine_id: routineId })
    });
    
    const data = await res.json();
    if (data.id) {
        window.location.href = `/workouts/${data.id}/edit`;
    }
}
</script>
@endsection
