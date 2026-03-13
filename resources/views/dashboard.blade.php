@extends('layouts.app')

@section('title', 'Dashboard')

@section('styles')
<style>
    /* Page Header with Logo */
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
    
    /* Stats Cards - White cards like mockup */
    .stats-row {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 12px;
        margin-bottom: 16px;
    }
    
    .stat-box {
        background: #fff;
        border-radius: 4px;
        padding: 20px;
        text-align: center;
    }
    
    .stat-box.full-width {
        grid-column: 1 / -1;
    }
    
    .stat-box-label {
        font-size: 11px;
        color: #666;
        text-transform: uppercase;
        letter-spacing: 1px;
        margin-bottom: 8px;
    }
    
    .stat-box-value {
        font-size: 36px;
        font-weight: 700;
        color: #1a1a1a;
    }
    
    .stat-box-value.small {
        font-size: 28px;
    }
    
    /* Action Buttons - GOALS and ROUTINES */
    .action-buttons {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 12px;
        margin-bottom: 24px;
    }
    
    .action-btn-card {
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 10px;
        padding: 16px;
        background: #fff;
        border: 2px solid #e0e0e0;
        border-radius: 4px;
        color: #1a1a1a;
        text-decoration: none;
        font-size: 12px;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 1px;
        transition: all 0.2s;
    }
    
    .action-btn-card:hover {
        border-color: var(--accent);
        transform: translateY(-2px);
    }
    
    .action-btn-card svg {
        width: 24px;
        height: 24px;
        color: var(--accent);
    }
    
    /* Section Title */
    .section-title {
        font-size: 12px;
        color: var(--text-dim);
        text-transform: uppercase;
        letter-spacing: 2px;
        margin-bottom: 16px;
    }
    
    /* Workout Cards */
    .workout-list {
        display: flex;
        flex-direction: column;
        gap: 12px;
    }
    
    .workout-item {
        background: #fff;
        border-radius: 4px;
        padding: 16px;
        text-decoration: none;
        color: inherit;
        display: block;
        transition: all 0.2s;
    }
    
    .workout-item:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(0,0,0,0.1);
    }
    
    .workout-item-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        margin-bottom: 8px;
    }
    
    .workout-item-title {
        font-size: 14px;
        font-weight: 700;
        color: #1a1a1a;
        text-transform: uppercase;
    }
    
    .workout-item-date {
        font-size: 11px;
        color: #666;
    }
    
    .workout-item-stats {
        display: flex;
        align-items: center;
        gap: 16px;
        font-size: 12px;
        color: #666;
        margin-bottom: 10px;
    }
    
    .workout-item-stat {
        display: flex;
        align-items: center;
        gap: 4px;
    }
    
    /* Muscle Tags with Icons */
    .muscle-tags {
        display: flex;
        flex-wrap: wrap;
        gap: 6px;
    }
    
    .muscle-tag {
        display: inline-flex;
        align-items: center;
        gap: 4px;
        padding: 4px 10px;
        border-radius: 4px;
        font-size: 10px;
        font-weight: 600;
        text-transform: lowercase;
    }
    
    .muscle-tag svg {
        width: 12px;
        height: 12px;
    }
    
    .muscle-tag.leg {
        background: #d4edda;
        color: #155724;
    }
    
    .muscle-tag.chest {
        background: #f8d7da;
        color: #721c24;
    }
    
    .muscle-tag.back {
        background: #fff3cd;
        color: #856404;
    }
    
    .muscle-tag.shoulders {
        background: #d1ecf1;
        color: #0c5460;
    }
    
    .muscle-tag.arms {
        background: #e2e3e5;
        color: #383d41;
    }
    
    /* Empty State */
    .empty-state {
        text-align: center;
        padding: 40px 20px;
        color: var(--text-dim);
    }
    
    .empty-state svg {
        width: 48px;
        height: 48px;
        margin: 0 auto 16px;
        opacity: 0.3;
    }
</style>
@endsection

@section('content')
@php
$stats = auth()->user()->workouts()->completed()->get();
$totalWorkouts = $stats->count();
$totalVolume = $stats->sum('volume');
$recentWorkouts = auth()->user()->workouts()->completed()->with('sets.exercise')->latest('started_at')->take(5)->get();
@endphp

<!-- Header with Logo -->
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
</div>

<!-- Stats Overview -->
<div class="stats-row">
    <div class="stat-box">
        <div class="stat-box-label">WORKOUTS</div>
        <div class="stat-box-value small">{{ $totalWorkouts }}</div>
    </div>
    <div class="stat-box">
        <div class="stat-box-label">TOTAL VOLUME</div>
        <div class="stat-box-value small">{{ number_format($totalVolume / 1000, 1) }}k</div>
    </div>
</div>

<!-- Action Buttons with Icons -->
<div class="action-buttons">
    <a href="/goals" class="action-btn-card">
        <!-- Target/Goal Icon -->
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <circle cx="12" cy="12" r="10"/>
            <circle cx="12" cy="12" r="6"/>
            <circle cx="12" cy="12" r="2"/>
        </svg>
        GOALS
    </a>
    <a href="/routines" class="action-btn-card">
        <!-- Calendar/Routine Icon -->
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <rect x="3" y="4" width="18" height="18" rx="2" ry="2"/>
            <line x1="16" y1="2" x2="16" y2="6"/>
            <line x1="8" y1="2" x2="8" y2="6"/>
            <line x1="3" y1="10" x2="21" y2="10"/>
        </svg>
        ROUTINES
    </a>
</div>

<!-- Recent Workouts -->
<p class="section-title">RECENT WORKOUTS</p>

@if($recentWorkouts->isEmpty())
    <div class="empty-state">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
            <path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"/>
        </svg>
        <p>No workouts yet. Start your first one!</p>
    </div>
@else
    <div class="workout-list">
        @foreach($recentWorkouts as $workout)
            @php
                $exercises = $workout->sets->groupBy('exercise.name');
                $muscleGroups = [];
                foreach ($exercises as $name => $sets) {
                    $nameLower = strtolower($name);
                    if (str_contains($nameLower, 'press') || str_contains($nameLower, 'fly') || str_contains($nameLower, 'chest')) {
                        $muscleGroups[] = 'chest';
                    } elseif (str_contains($nameLower, 'row') || str_contains($nameLower, 'pull') || str_contains($nameLower, 'lat') || str_contains($nameLower, 'back')) {
                        $muscleGroups[] = 'back';
                    } elseif (str_contains($nameLower, 'squat') || str_contains($nameLower, 'leg') || str_contains($nameLower, 'calf')) {
                        $muscleGroups[] = 'leg';
                    } elseif (str_contains($nameLower, 'shoulder') || str_contains($nameLower, 'raise')) {
                        $muscleGroups[] = 'shoulders';
                    } elseif (str_contains($nameLower, 'curl') || str_contains($nameLower, 'bicep') || str_contains($nameLower, 'tricep')) {
                        $muscleGroups[] = 'arms';
                    }
                }
                $muscleGroups = array_unique($muscleGroups);
                $timestamp = $workout->started_at / 1000;
            @endphp
            <a href="/workouts/{{ $workout->id }}" class="workout-item">
                <div class="workout-item-header">
                    <div>
                        <div class="workout-item-title">Workout #{{ $workout->id }} — {{ date('l', $timestamp) }}</div>
                        <div class="workout-item-date">{{ date('M j, Y', $timestamp) }}</div>
                    </div>
                </div>
                <div class="workout-item-stats">
                    <span class="workout-item-stat">
                        <!-- Dumbbell Icon -->
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M6 5v14M18 5v14M3 8h18M3 16h18"/>
                        </svg>
                        {{ $workout->sets->count() }} sets
                    </span>
                    <span class="workout-item-stat">
                        <!-- Chart/Volume Icon -->
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <line x1="18" y1="20" x2="18" y2="10"/>
                            <line x1="12" y1="20" x2="12" y2="4"/>
                            <line x1="6" y1="20" x2="6" y2="14"/>
                        </svg>
                        {{ number_format($workout->volume / 1000, 1) }}k lbs
                    </span>
                </div>
                @if(!empty($muscleGroups))
                    <div class="muscle-tags">
                        @foreach($muscleGroups as $muscle)
                            <span class="muscle-tag {{ $muscle }}">
                                @if($muscle === 'leg')
                                    <!-- Leg Icon -->
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                        <path d="M12 3v18M8 21l4-4 4 4M8 3l4 4 4-4"/>
                                    </svg>
                                @elseif($muscle === 'chest')
                                    <!-- Chest Icon -->
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                        <path d="M20 12V8h-4V4h-4v4H8V4H4v8h4v4h4v4h4v-4h4v-4z"/>
                                    </svg>
                                @elseif($muscle === 'back')
                                    <!-- Back Icon -->
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                        <path d="M12 2L2 7l10 5 10-5-10-5zM2 17l10 5 10-5M2 12l10 5 10-5"/>
                                    </svg>
                                @elseif($muscle === 'shoulders')
                                    <!-- Shoulders Icon -->
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                        <circle cx="12" cy="12" r="3"/>
                                        <path d="M12 1v6m0 6v6m11-7h-6m-6 0H1"/>
                                    </svg>
                                @elseif($muscle === 'arms')
                                    <!-- Arms Icon -->
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                        <path d="M8 6v12M16 6v12M4 10h16M4 14h16"/>
                                    </svg>
                                @endif
                                {{ $muscle }}
                            </span>
                        @endforeach
                    </div>
                @endif
            </a>
        @endforeach
    </div>
@endif
@endsection
