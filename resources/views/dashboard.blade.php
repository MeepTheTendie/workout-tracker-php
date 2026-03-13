@extends('layouts.app')

@section('title', 'Dashboard')

@section('styles')
<style>
    .dashboard-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        margin-bottom: 20px;
    }
    
    .dashboard-logo {
        display: flex;
        align-items: center;
        gap: 10px;
    }
    
    .dashboard-logo svg {
        width: 32px;
        height: 20px;
        fill: var(--accent);
    }
    
    .dashboard-logo span {
        font-size: 14px;
        font-weight: 700;
        letter-spacing: 2px;
    }
    
    .stats-overview {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 12px;
        margin-bottom: 20px;
    }
    
    .stat-card {
        background: var(--surface);
        border: 1px solid var(--border);
        border-radius: 10px;
        padding: 16px;
        text-align: center;
    }
    
    .stat-card.primary {
        grid-column: 1 / -1;
        text-align: left;
        position: relative;
        overflow: hidden;
    }
    
    .stat-card.primary::before {
        content: '';
        position: absolute;
        top: 0;
        right: 0;
        width: 100px;
        height: 100%;
        background: linear-gradient(90deg, transparent, rgba(255,107,53,0.1));
    }
    
    .stat-label {
        font-size: 10px;
        color: var(--text-dim);
        text-transform: uppercase;
        letter-spacing: 1px;
        margin-bottom: 4px;
    }
    
    .stat-value {
        font-size: 28px;
        font-weight: 700;
        color: var(--text);
    }
    
    .stat-value.small {
        font-size: 24px;
    }
    
    .trend-chart-mini {
        display: flex;
        align-items: flex-end;
        gap: 3px;
        height: 40px;
        margin-top: 12px;
    }
    
    .trend-bar-mini {
        flex: 1;
        background: var(--accent);
        border-radius: 2px 2px 0 0;
        opacity: 0.6;
        min-height: 4px;
        transition: opacity 0.2s;
    }
    
    .trend-bar-mini:hover {
        opacity: 1;
    }
    
    .quick-actions {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 12px;
        margin-bottom: 20px;
    }
    
    .action-btn {
        display: flex;
        align-items: center;
        gap: 10px;
        padding: 14px 16px;
        background: var(--surface);
        border: 1px solid var(--border);
        border-radius: 10px;
        color: var(--text);
        text-decoration: none;
        transition: all 0.2s;
    }
    
    .action-btn:hover {
        background: var(--surface-hover);
        border-color: var(--accent);
    }
    
    .action-btn svg {
        width: 20px;
        height: 20px;
        color: var(--accent);
    }
    
    .action-btn-text {
        font-size: 12px;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }
    
    .section-header-with-icon {
        display: flex;
        align-items: center;
        gap: 8px;
        margin-bottom: 16px;
        font-size: 12px;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 1px;
        color: var(--text-dim);
    }
    
    .section-header-with-icon svg {
        width: 16px;
        height: 16px;
    }
    
    .workout-card {
        background: var(--surface);
        border: 1px solid var(--border);
        border-radius: 10px;
        padding: 16px;
        margin-bottom: 12px;
        text-decoration: none;
        color: inherit;
        display: block;
        transition: all 0.2s;
    }
    
    .workout-card:hover {
        background: var(--surface-hover);
        border-color: var(--accent);
        transform: translateY(-2px);
    }
    
    .workout-card-header {
        display: flex;
        align-items: flex-start;
        justify-content: space-between;
        margin-bottom: 12px;
    }
    
    .workout-card-title {
        font-size: 13px;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        margin-bottom: 4px;
    }
    
    .workout-card-date {
        font-size: 11px;
        color: var(--text-dim);
    }
    
    .workout-card-volume {
        text-align: right;
    }
    
    .workout-card-volume-value {
        font-size: 18px;
        font-weight: 700;
        color: var(--accent);
    }
    
    .workout-card-volume-label {
        font-size: 10px;
        color: var(--text-dim);
        text-transform: uppercase;
    }
    
    .workout-card-meta {
        display: flex;
        align-items: center;
        gap: 12px;
        font-size: 11px;
        color: var(--text-dim);
    }
    
    .workout-card-meta-item {
        display: flex;
        align-items: center;
        gap: 4px;
    }
    
    .muscle-chips {
        display: flex;
        flex-wrap: wrap;
        gap: 6px;
        margin-top: 10px;
    }
    
    .muscle-chip {
        display: inline-flex;
        align-items: center;
        gap: 4px;
        padding: 4px 8px;
        background: var(--bg);
        border-radius: 4px;
        font-size: 10px;
        color: var(--text-dim);
    }
    
    .muscle-chip svg {
        width: 12px;
        height: 12px;
    }
    
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

// Get volume trend for last 7 days
$volumeTrend = [];
$startOfWeek = now()->subDays(6)->startOfDay()->timestamp * 1000;
$weekWorkouts = auth()->user()->workouts()
    ->completed()
    ->where('started_at', '>=', $startOfWeek)
    ->with('sets')
    ->get();

for ($i = 6; $i >= 0; $i--) {
    $date = now()->subDays($i);
    $dayStart = $date->startOfDay()->timestamp * 1000;
    $dayEnd = $date->endOfDay()->timestamp * 1000;
    
    $dayWorkouts = $weekWorkouts->filter(function ($w) use ($dayStart, $dayEnd) {
        return $w->started_at >= $dayStart && $w->started_at <= $dayEnd;
    });
    
    $dayVolume = $dayWorkouts->sum(function ($w) {
        return $w->sets->sum(fn($s) => ($s->weight ?? 0) * ($s->reps ?? 0));
    });
    $volumeTrend[] = $dayVolume;
}
$maxTrendVolume = max($volumeTrend) ?: 1;
@endphp

<!-- Header with Logo -->
<div class="dashboard-header">
    <div class="dashboard-logo">
        <svg viewBox="0 0 80 48" fill="none" xmlns="http://www.w3.org/2000/svg">
            <rect x="12" y="22" width="56" height="4"/>
            <rect x="4" y="14" width="4" height="20"/>
            <rect x="10" y="10" width="4" height="28"/>
            <rect x="16" y="16" width="4" height="16"/>
            <rect x="72" y="14" width="4" height="20"/>
            <rect x="66" y="10" width="4" height="28"/>
            <rect x="60" y="16" width="4" height="16"/>
        </svg>
        <span>WORKOUT TRACKER</span>
    </div>
</div>

<!-- Stats Overview with Trend Chart -->
<div class="stats-overview">
    <div class="stat-card primary">
        <div class="stat-label">Total Volume</div>
        <div class="stat-value">{{ number_format($totalVolume / 1000, 1) }}k <span style="font-size: 14px; color: var(--text-dim);">lbs</span></div>
        <div class="trend-chart-mini">
            @foreach($volumeTrend as $vol)
                @php $height = min(100, ($vol / $maxTrendVolume) * 100); @endphp
                <div class="trend-bar-mini" style="height: {{ max(4, $height) }}%"></div>
            @endforeach
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-label">Workouts</div>
        <div class="stat-value small">{{ $totalWorkouts }}</div>
    </div>
    <div class="stat-card">
        <div class="stat-label">This Week</div>
        <div class="stat-value small">
            {{ auth()->user()->workouts()->completed()->where('started_at', '>=', now()->startOfWeek()->timestamp * 1000)->count() }}
        </div>
    </div>
</div>

<!-- Quick Actions -->
<div class="quick-actions">
    <a href="/goals" class="action-btn">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <circle cx="12" cy="12" r="10"/>
            <circle cx="12" cy="12" r="6"/>
            <circle cx="12" cy="12" r="2"/>
        </svg>
        <span class="action-btn-text">Goals</span>
    </a>
    <a href="/routines" class="action-btn">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <rect x="3" y="4" width="18" height="18" rx="2" ry="2"/>
            <line x1="16" y1="2" x2="16" y2="6"/>
            <line x1="8" y1="2" x2="8" y2="6"/>
            <line x1="3" y1="10" x2="21" y2="10"/>
        </svg>
        <span class="action-btn-text">Routines</span>
    </a>
</div>

<!-- Recent Workouts -->
<div class="section-header-with-icon">
    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
        <polyline points="22 12 18 12 15 21 9 3 6 12 2 12"/>
    </svg>
    Recent Workouts
</div>

@if($recentWorkouts->isEmpty())
    <div class="empty-state">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
            <path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"/>
        </svg>
        <p>No workouts yet. Start your first one!</p>
    </div>
@else
    @foreach($recentWorkouts as $workout)
        @php
            $exercises = $workout->sets->groupBy('exercise.name');
            $muscleGroups = [];
            foreach ($exercises as $name => $sets) {
                // Simple muscle group detection based on exercise name
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
        @endphp
        <a href="/workouts/{{ $workout->id }}" class="workout-card">
            <div class="workout-card-header">
                <div>
                    <div class="workout-card-title">Workout #{{ $workout->id }} — {{ date('l', $workout->started_at / 1000) }}</div>
                    <div class="workout-card-date">{{ date('M j, Y', $workout->started_at / 1000) }}</div>
                </div>
                <div class="workout-card-volume">
                    <div class="workout-card-volume-value">{{ number_format($workout->volume / 1000, 1) }}k</div>
                    <div class="workout-card-volume-label">lbs</div>
                </div>
            </div>
            <div class="workout-card-meta">
                <span class="workout-card-meta-item">
                    <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <circle cx="12" cy="12" r="10"/>
                        <polyline points="12 6 12 12 16 14"/>
                    </svg>
                    {{ $workout->duration ?? 0 }} min
                </span>
                <span class="workout-card-meta-item">
                    <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M6 5v14M18 5v14M3 8h18M3 16h18"/>
                    </svg>
                    {{ $workout->sets->count() }} sets
                </span>
            </div>
            @if(!empty($muscleGroups))
                <div class="muscle-chips">
                    @foreach(array_slice($muscleGroups, 0, 3) as $muscle)
                        <span class="muscle-chip">
                            @if($muscle === 'chest')
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20 12V8h-4V4h-4v4H8V4H4v8h4v4h4v4h4v-4h4v-4z"/></svg>
                            @elseif($muscle === 'back')
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 2L2 7l10 5 10-5-10-5zM2 17l10 5 10-5M2 12l10 5 10-5"/></svg>
                            @elseif($muscle === 'legs')
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 3v18M8 21l4-4 4 4M8 3l4 4 4-4"/></svg>
                            @elseif($muscle === 'shoulders')
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="3"/><path d="M12 1v6m0 6v6m11-7h-6m-6 0H1"/></svg>
                            @elseif($muscle === 'arms')
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M8 6v12M16 6v12M4 10h16M4 14h16"/></svg>
                            @elseif($muscle === 'core')
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="9"/><circle cx="12" cy="12" r="4"/></svg>
                            @endif
                            {{ $muscle }}
                        </span>
                    @endforeach
                    @if(count($muscleGroups) > 3)
                        <span class="muscle-chip">+{{ count($muscleGroups) - 3 }}</span>
                    @endif
                </div>
            @endif
        </a>
    @endforeach
@endif
@endsection
