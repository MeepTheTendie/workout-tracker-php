@extends('layouts.app')

@section('title', 'Workout History')

@section('styles')
<style>
    .page-header-history {
        display: flex;
        align-items: center;
        gap: 12px;
        margin-bottom: 20px;
        padding-bottom: 16px;
        border-bottom: 1px solid var(--border);
    }
    
    .page-header-history svg {
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
    
    /* Filter Bar */
    .filter-bar {
        display: flex;
        gap: 8px;
        margin-bottom: 20px;
        overflow-x: auto;
        padding-bottom: 4px;
        scrollbar-width: none;
    }
    
    .filter-bar::-webkit-scrollbar {
        display: none;
    }
    
    .filter-btn {
        padding: 8px 16px;
        background: var(--surface);
        border: 1px solid var(--border);
        border-radius: 20px;
        font-family: 'Space Mono', monospace;
        font-size: 11px;
        color: var(--text-dim);
        cursor: pointer;
        white-space: nowrap;
        transition: all 0.2s;
    }
    
    .filter-btn:hover, .filter-btn.active {
        background: var(--accent);
        border-color: var(--accent);
        color: #fff;
    }
    
    /* Search */
    .search-box {
        position: relative;
        margin-bottom: 20px;
    }
    
    .search-box svg {
        position: absolute;
        left: 14px;
        top: 50%;
        transform: translateY(-50%);
        width: 18px;
        height: 18px;
        color: var(--text-muted);
    }
    
    .search-box input {
        width: 100%;
        padding: 12px 14px 12px 44px;
        background: var(--surface);
        border: 1px solid var(--border);
        border-radius: 8px;
        font-family: 'Space Mono', monospace;
        font-size: 14px;
        color: var(--text);
    }
    
    .search-box input::placeholder {
        color: var(--text-muted);
    }
    
    /* Workout Cards */
    .workout-list {
        display: flex;
        flex-direction: column;
        gap: 12px;
    }
    
    .workout-card {
        background: var(--surface);
        border: 1px solid var(--border);
        border-radius: 12px;
        overflow: hidden;
        text-decoration: none;
        color: inherit;
        display: block;
        transition: all 0.2s;
    }
    
    .workout-card:hover {
        border-color: var(--accent);
        transform: translateY(-2px);
    }
    
    .workout-card-header {
        display: flex;
        align-items: flex-start;
        justify-content: space-between;
        padding: 16px;
        border-bottom: 1px solid var(--border);
    }
    
    .workout-date-group {
        display: flex;
        align-items: center;
        gap: 12px;
    }
    
    .workout-day-badge {
        width: 48px;
        height: 48px;
        background: var(--bg);
        border-radius: 10px;
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
    }
    
    .workout-day-name {
        font-size: 10px;
        font-weight: 700;
        text-transform: uppercase;
        color: var(--accent);
    }
    
    .workout-day-number {
        font-size: 18px;
        font-weight: 700;
    }
    
    .workout-title-group h3 {
        font-size: 14px;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        margin-bottom: 2px;
    }
    
    .workout-title-group p {
        font-size: 11px;
        color: var(--text-dim);
    }
    
    .workout-volume {
        text-align: right;
    }
    
    .workout-volume-value {
        font-size: 20px;
        font-weight: 700;
        color: var(--accent);
    }
    
    .workout-volume-label {
        font-size: 10px;
        color: var(--text-dim);
        text-transform: uppercase;
    }
    
    .workout-card-body {
        padding: 12px 16px;
    }
    
    .workout-meta-row {
        display: flex;
        align-items: center;
        gap: 16px;
        margin-bottom: 10px;
        font-size: 11px;
        color: var(--text-dim);
    }
    
    .workout-meta-item {
        display: flex;
        align-items: center;
        gap: 4px;
    }
    
    .workout-meta-item svg {
        width: 14px;
        height: 14px;
    }
    
    .workout-exercises-preview {
        font-size: 12px;
        color: var(--text);
        line-height: 1.5;
    }
    
    .workout-exercises-preview strong {
        color: var(--accent);
    }
    
    /* Muscle Tags */
    .muscle-tags {
        display: flex;
        flex-wrap: wrap;
        gap: 6px;
        margin-top: 10px;
    }
    
    .muscle-tag {
        display: inline-flex;
        align-items: center;
        gap: 4px;
        padding: 4px 8px;
        background: var(--bg);
        border-radius: 4px;
        font-size: 10px;
        color: var(--text-dim);
    }
    
    .muscle-tag svg {
        width: 12px;
        height: 12px;
    }
    
    /* Empty State */
    .empty-state {
        text-align: center;
        padding: 60px 20px;
        color: var(--text-dim);
    }
    
    .empty-state svg {
        width: 56px;
        height: 56px;
        margin: 0 auto 20px;
        opacity: 0.3;
    }
    
    .empty-state h3 {
        font-size: 14px;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 1px;
        margin-bottom: 8px;
        color: var(--text);
    }
    
    .empty-state p {
        font-size: 12px;
        margin-bottom: 20px;
    }
    
    /* Load More */
    .load-more {
        text-align: center;
        padding: 20px;
    }
    
    .load-more-btn {
        padding: 12px 24px;
        background: var(--surface);
        border: 1px solid var(--border);
        border-radius: 8px;
        font-family: 'Space Mono', monospace;
        font-size: 12px;
        color: var(--text);
        cursor: pointer;
        transition: all 0.2s;
    }
    
    .load-more-btn:hover {
        border-color: var(--accent);
        color: var(--accent);
    }
</style>
@endsection

@section('content')
@php
$workouts = Auth::user()->workouts()
    ->completed()
    ->with('sets.exercise')
    ->orderBy('started_at', 'desc')
    ->get();
@endphp

<!-- Header with Branding -->
<div class="page-header-history">
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
        <h1>WORKOUT HISTORY</h1>
        <p>Your journey, documented.</p>
    </div>
</div>

<!-- Filters -->
<div class="filter-bar">
    <button class="filter-btn active" data-filter="all">All Time</button>
    <button class="filter-btn" data-filter="week">This Week</button>
    <button class="filter-btn" data-filter="month">This Month</button>
    <button class="filter-btn" data-filter="year">This Year</button>
</div>

<!-- Search -->
<div class="search-box">
    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
        <circle cx="11" cy="11" r="8"/>
        <path d="m21 21-4.35-4.35"/>
    </svg>
    <input type="text" id="workoutSearch" placeholder="Search workouts, exercises..." onkeyup="filterWorkouts()">
</div>

@if($workouts->isEmpty())
    <div class="empty-state">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
            <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/>
            <polyline points="14 2 14 8 20 8"/>
            <line x1="16" y1="13" x2="8" y2="13"/>
            <line x1="16" y1="17" x2="8" y2="17"/>
            <polyline points="10 9 9 9 8 9"/>
        </svg>
        <h3>No Workouts Yet</h3>
        <p>Start logging your first workout to see your history here.</p>
        <a href="/workouts/create" class="btn">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="vertical-align: middle; margin-right: 6px;">
                <circle cx="12" cy="12" r="10"/>
                <line x1="12" y1="8" x2="12" y2="16"/>
                <line x1="8" y1="12" x2="16" y2="12"/>
            </svg>
            Start Workout
        </a>
    </div>
@else
    <div class="workout-list" id="workoutList">
        @foreach($workouts as $workout)
            @php
                $exercises = $workout->sets->groupBy('exercise.name');
                $exerciseNames = $exercises->keys()->take(3);
                $muscleGroups = [];
                
                foreach ($exercises as $name => $sets) {
                    $nameLower = strtolower($name);
                    if (str_contains($nameLower, 'press') || str_contains($nameLower, 'fly') || str_contains($nameLower, 'chest')) {
                        $muscleGroups[] = 'chest';
                    } elseif (str_contains($nameLower, 'row') || str_contains($nameLower, 'pull') || str_contains($nameLower, 'lat') || str_contains($nameLower, 'back')) {
                        $muscleGroups[] = 'back';
                    } elseif (str_contains($nameLower, 'squat') || str_contains($nameLower, 'leg') || str_contains($nameLower, 'calf')) {
                        $muscleGroups[] = 'legs';
                    } elseif (str_contains($nameLower, 'shoulder') || str_contains($nameLower, 'press') || str_contains($nameLower, 'raise')) {
                        $muscleGroups[] = 'shoulders';
                    } elseif (str_contains($nameLower, 'curl') || str_contains($nameLower, 'bicep') || str_contains($nameLower, 'tricep')) {
                        $muscleGroups[] = 'arms';
                    } elseif (str_contains($nameLower, 'crunch') || str_contains($nameLower, 'plank') || str_contains($nameLower, 'torso')) {
                        $muscleGroups[] = 'core';
                    }
                }
                $muscleGroups = array_unique($muscleGroups);
                
                $timestamp = $workout->started_at / 1000;
                $dayName = date('D', $timestamp);
                $dayNum = date('j', $timestamp);
            @endphp
            <a href="/workouts/{{ $workout->id }}" class="workout-card" data-workout="{{ strtolower($workout->id . ' ' . implode(' ', $exerciseNames->toArray())) }}">
                <div class="workout-card-header">
                    <div class="workout-date-group">
                        <div class="workout-day-badge">
                            <span class="workout-day-name">{{ $dayName }}</span>
                            <span class="workout-day-number">{{ $dayNum }}</span>
                        </div>
                        <div class="workout-title-group">
                            <h3>Workout #{{ $workout->id }}</h3>
                            <p>{{ date('F Y', $timestamp) }}</p>
                        </div>
                    </div>
                    <div class="workout-volume">
                        <div class="workout-volume-value">{{ number_format($workout->volume / 1000, 1) }}k</div>
                        <div class="workout-volume-label">lbs</div>
                    </div>
                </div>
                <div class="workout-card-body">
                    <div class="workout-meta-row">
                        <span class="workout-meta-item">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M6 9H4.5a2.5 2.5 0 0 1 0-5H6"/>
                                <path d="M18 9h1.5a2.5 2.5 0 0 0 0-5H18"/>
                                <path d="M4 22h16"/>
                                <path d="M10 14.66V17c0 .55-.47.98-.97 1.21C7.85 18.75 7 20.24 7 22"/>
                                <path d="M14 14.66V17c0 .55.47.98.97 1.21C16.15 18.75 17 20.24 17 22"/>
                                <path d="M18 2H6v7a6 6 0 0 0 12 0V2Z"/>
                            </svg>
                            {{ $workout->sets->count() }} sets
                        </span>
                        <span class="workout-meta-item">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M20.42 4.58a5.4 5.4 0 0 0-7.65 0l-.77.78-.77-.78a5.4 5.4 0 0 0-7.65 0C1.46 6.7 1.33 10.28 4 13l8 8 8-8c2.67-2.72 2.54-6.3.42-8.42z"/>
                            </svg>
                            {{ $exercises->count() }} exercises
                        </span>
                    </div>
                    <div class="workout-exercises-preview">
                        <strong>{{ $exerciseNames->join(', ') }}</strong>
                        @if($exercises->count() > 3)
                            <span style="color: var(--text-dim);"> +{{ $exercises->count() - 3 }} more</span>
                        @endif
                    </div>
                    @if(!empty($muscleGroups))
                        <div class="muscle-tags">
                            @foreach(array_slice($muscleGroups, 0, 4) as $muscle)
                                <span class="muscle-tag">{{ $muscle }}</span>
                            @endforeach
                        </div>
                    @endif
                </div>
            </a>
        @endforeach
    </div>
@endif
@endsection

@section('scripts')
<script>
// Filter buttons
const filterBtns = document.querySelectorAll('.filter-btn');
filterBtns.forEach(btn => {
    btn.addEventListener('click', () => {
        filterBtns.forEach(b => b.classList.remove('active'));
        btn.classList.add('active');
        // Filter logic would go here
    });
});

// Search filter
function filterWorkouts() {
    const searchTerm = document.getElementById('workoutSearch').value.toLowerCase();
    const cards = document.querySelectorAll('.workout-card');
    
    cards.forEach(card => {
        const data = card.getAttribute('data-workout');
        if (data && data.includes(searchTerm)) {
            card.style.display = 'block';
        } else {
            card.style.display = 'none';
        }
    });
}
</script>
@endsection
