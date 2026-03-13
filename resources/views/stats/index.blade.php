@extends('layouts.app')

@section('title', 'Workout Stats')

@section('styles')
<style>
    .page-header-stats {
        display: flex;
        align-items: center;
        gap: 12px;
        margin-bottom: 20px;
        padding-bottom: 16px;
        border-bottom: 1px solid var(--border);
    }
    
    .page-header-stats svg {
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
    
    /* Recent Activity Summary */
    .activity-summary {
        display: grid;
        grid-template-columns: repeat(3, 1fr);
        gap: 12px;
        margin-bottom: 20px;
    }
    
    .activity-card {
        background: var(--surface);
        border: 1px solid var(--border);
        border-radius: 10px;
        padding: 16px 8px;
        text-align: center;
    }
    
    .activity-card-icon {
        width: 32px;
        height: 32px;
        margin: 0 auto 10px;
        background: var(--bg);
        border-radius: 8px;
        display: flex;
        align-items: center;
        justify-content: center;
    }
    
    .activity-card-icon svg {
        width: 18px;
        height: 18px;
        color: var(--accent);
    }
    
    .activity-card-value {
        font-size: 20px;
        font-weight: 700;
        margin-bottom: 4px;
    }
    
    .activity-card-label {
        font-size: 9px;
        color: var(--text-dim);
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }
    
    /* Volume by Exercise Section */
    .volume-section {
        background: var(--surface);
        border: 1px solid var(--border);
        border-radius: 12px;
        padding: 16px;
        margin-bottom: 20px;
    }
    
    .volume-section-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        margin-bottom: 16px;
    }
    
    .volume-section-title {
        display: flex;
        align-items: center;
        gap: 8px;
        font-size: 12px;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 1px;
        color: var(--text-dim);
    }
    
    .volume-section-title svg {
        width: 16px;
        height: 16px;
    }
    
    .volume-filter-btn {
        padding: 6px 12px;
        background: var(--bg);
        border: 1px solid var(--border);
        border-radius: 6px;
        font-family: 'Space Mono', monospace;
        font-size: 10px;
        color: var(--text-dim);
        cursor: pointer;
        transition: all 0.2s;
    }
    
    .volume-filter-btn:hover {
        border-color: var(--accent);
        color: var(--accent);
    }
    
    .volume-bar-item {
        margin-bottom: 14px;
    }
    
    .volume-bar-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 6px;
    }
    
    .volume-bar-label {
        display: flex;
        align-items: center;
        gap: 8px;
        font-size: 12px;
        font-weight: 600;
        text-transform: uppercase;
    }
    
    .volume-bar-value {
        font-size: 11px;
        color: var(--text-dim);
    }
    
    .volume-bar-track {
        height: 8px;
        background: var(--bg);
        border-radius: 4px;
        overflow: hidden;
    }
    
    .volume-bar-fill {
        height: 100%;
        background: linear-gradient(90deg, var(--accent) 0%, var(--accent-hover) 100%);
        border-radius: 4px;
        transition: width 0.5s ease;
    }
    
    /* Muscle Group Panels */
    .muscle-panel {
        background: var(--surface);
        border: 1px solid var(--border);
        border-radius: 12px;
        margin-bottom: 12px;
        overflow: hidden;
    }
    
    .muscle-panel-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 16px;
        cursor: pointer;
        transition: background 0.2s;
    }
    
    .muscle-panel-header:hover {
        background: var(--surface-hover);
    }
    
    .muscle-panel-title {
        display: flex;
        align-items: center;
        gap: 12px;
    }
    
    .muscle-panel-icon {
        width: 36px;
        height: 36px;
        background: var(--bg);
        border-radius: 8px;
        display: flex;
        align-items: center;
        justify-content: center;
    }
    
    .muscle-panel-icon svg {
        width: 20px;
        height: 20px;
        color: var(--accent);
    }
    
    .muscle-panel-info h3 {
        font-size: 14px;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        margin-bottom: 2px;
    }
    
    .muscle-panel-info p {
        font-size: 11px;
        color: var(--text-dim);
    }
    
    .muscle-panel-expand {
        width: 20px;
        height: 20px;
        color: var(--text-dim);
        transition: transform 0.2s;
    }
    
    .muscle-panel.expanded .muscle-panel-expand {
        transform: rotate(180deg);
    }
    
    .muscle-panel-body {
        display: none;
        border-top: 1px solid var(--border);
    }
    
    .muscle-panel.expanded .muscle-panel-body {
        display: block;
    }
    
    /* Exercise History Items */
    .exercise-history-item {
        display: grid;
        grid-template-columns: 1fr auto auto auto;
        gap: 12px;
        padding: 12px 16px;
        border-bottom: 1px solid var(--border);
        font-size: 12px;
        align-items: center;
    }
    
    .exercise-history-item:last-child {
        border-bottom: none;
    }
    
    .exercise-history-name {
        display: flex;
        align-items: center;
        gap: 8px;
        font-weight: 600;
    }
    
    .exercise-history-name svg {
        width: 14px;
        height: 14px;
        color: var(--accent);
    }
    
    .exercise-history-date {
        font-size: 10px;
        color: var(--text-dim);
    }
    
    .exercise-history-sets {
        font-size: 11px;
        color: var(--text-dim);
    }
    
    .exercise-history-volume {
        font-weight: 700;
        color: var(--accent);
        text-align: right;
        min-width: 60px;
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
</style>
@endsection

@section('content')
@php
$workouts = auth()->user()->workouts()->completed()->with('sets')->get();
$sets = App\Models\WorkoutSet::whereHas('workout', function($q) {
    $q->where('user_id', auth()->id());
})->with('exercise')->get();

$totalWorkouts = $workouts->count();
$totalVolume = $sets->sum(fn($s) => ($s->weight ?? 0) * ($s->reps ?? 0));

$thirtyDaysAgo = (time() - 30 * 86400) * 1000;
$recentWorkouts = $workouts->filter(fn($w) => $w->started_at >= $thirtyDaysAgo);
$recentWorkoutIds = $recentWorkouts->pluck('id');
$recentSets = $sets->filter(fn($s) => $recentWorkoutIds->contains($s->workout_id));
$recentVolume = $recentSets->sum(fn($s) => ($s->weight ?? 0) * ($s->reps ?? 0));

// Calculate streak
$workoutDates = $workouts->map(fn($w) => date('Y-m-d', $w->started_at / 1000))->unique()->sort()->values();
$streak = 0;
$today = date('Y-m-d');

foreach ($workoutDates->reverse() as $i => $date) {
    $diff = (strtotime($today) - strtotime($date)) / 86400;
    if ($i === 0 && $diff <= 1) {
        $streak = 1;
    } elseif ($diff == $i) {
        $streak = $i + 1;
    } else {
        break;
    }
}

// Volume by exercise
$volumeByExercise = [];
foreach ($sets->groupBy('exercise_id') as $exId => $exSets) {
    $exName = $exSets->first()->exercise->name ?? 'Unknown';
    $volume = $exSets->sum(fn($s) => ($s->weight ?? 0) * ($s->reps ?? 0));
    $volumeByExercise[] = [
        'exercise_id' => $exId,
        'exercise_name' => $exName,
        'total_sets' => $exSets->count(),
        'volume' => $volume
    ];
}
usort($volumeByExercise, fn($a, $b) => $b['volume'] - $a['volume']);
$maxVol = !empty($volumeByExercise) ? max(array_column($volumeByExercise, 'volume')) : 0;

// Group by muscle
$muscleGroups = [
    'chest' => ['name' => 'Chest', 'icon' => 'chest', 'exercises' => [], 'total_volume' => 0],
    'back' => ['name' => 'Back', 'icon' => 'back', 'exercises' => [], 'total_volume' => 0],
    'legs' => ['name' => 'Legs', 'icon' => 'legs', 'exercises' => [], 'total_volume' => 0],
    'shoulders' => ['name' => 'Shoulders', 'icon' => 'shoulders', 'exercises' => [], 'total_volume' => 0],
    'arms' => ['name' => 'Arms', 'icon' => 'arms', 'exercises' => [], 'total_volume' => 0],
    'core' => ['name' => 'Core', 'icon' => 'core', 'exercises' => [], 'total_volume' => 0],
];

foreach ($volumeByExercise as $exData) {
    $nameLower = strtolower($exData['exercise_name']);
    $group = 'chest';
    
    if (str_contains($nameLower, 'row') || str_contains($nameLower, 'pull') || str_contains($nameLower, 'lat') || str_contains($nameLower, 'deadlift') || str_contains($nameLower, 'back')) {
        $group = 'back';
    } elseif (str_contains($nameLower, 'squat') || str_contains($nameLower, 'leg') || str_contains($nameLower, 'calf')) {
        $group = 'legs';
    } elseif (str_contains($nameLower, 'shoulder') || str_contains($nameLower, 'raise')) {
        $group = 'shoulders';
    } elseif (str_contains($nameLower, 'curl') || str_contains($nameLower, 'bicep') || str_contains($nameLower, 'tricep') || str_contains($nameLower, 'dip')) {
        $group = 'arms';
    } elseif (str_contains($nameLower, 'crunch') || str_contains($nameLower, 'plank') || str_contains($nameLower, 'torso') || str_contains($nameLower, 'abdominal')) {
        $group = 'core';
    }
    
    $muscleGroups[$group]['exercises'][] = $exData;
    $muscleGroups[$group]['total_volume'] += $exData['volume'];
}

function formatVolume($vol) {
    if ($vol >= 1000000) return round($vol / 1000000, 1) . 'M';
    if ($vol >= 1000) return round($vol / 1000, 1) . 'k';
    return $vol;
}
@endphp

<!-- Header with Branding -->
<div class="page-header-stats">
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
        <h1>WORKOUT STATS</h1>
        <p>Numbers don't lie.</p>
    </div>
</div>

@if($workouts->isEmpty())
    <div class="empty-state">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
            <line x1="18" y1="20" x2="18" y2="10"/>
            <line x1="12" y1="20" x2="12" y2="4"/>
            <line x1="6" y1="20" x2="6" y2="14"/>
        </svg>
        <h3>No Stats Yet</h3>
        <p>Complete some workouts to see your statistics.</p>
        <a href="/workouts/create" class="btn">Start Workout</a>
    </div>
@else
    <!-- Recent Activity Summary -->
    <div class="activity-summary">
        <div class="activity-card">
            <div class="activity-card-icon">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <rect x="3" y="4" width="18" height="18" rx="2" ry="2"/>
                    <line x1="16" y1="2" x2="16" y2="6"/>
                    <line x1="8" y1="2" x2="8" y2="6"/>
                    <line x1="3" y1="10" x2="21" y2="10"/>
                </svg>
            </div>
            <div class="activity-card-value">{{ $totalWorkouts }}</div>
            <div class="activity-card-label">Total Workouts</div>
        </div>
        <div class="activity-card">
            <div class="activity-card-icon">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M12 2v20M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/>
                </svg>
            </div>
            <div class="activity-card-value">{{ formatVolume($totalVolume) }}</div>
            <div class="activity-card-label">Total Volume</div>
        </div>
        <div class="activity-card">
            <div class="activity-card-icon">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <polyline points="23 6 13.5 15.5 8.5 10.5 1 18"/>
                    <polyline points="17 6 23 6 23 12"/>
                </svg>
            </div>
            <div class="activity-card-value">{{ $streak }}</div>
            <div class="activity-card-label">Day Streak</div>
        </div>
    </div>
    
    <!-- Volume by Exercise -->
    <div class="volume-section">
        <div class="volume-section-header">
            <span class="volume-section-title">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M12 2v20M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/>
                </svg>
                Volume by Exercise
            </span>
            <button class="volume-filter-btn">All Time</button>
        </div>
        
        @foreach(array_slice($volumeByExercise, 0, 10) as $ex)
            <div class="volume-bar-item">
                <div class="volume-bar-header">
                    <span class="volume-bar-label">{{ strtoupper($ex['exercise_name']) }}</span>
                    <span class="volume-bar-value">{{ number_format($ex['volume']) }} lbs</span>
                </div>
                <div class="volume-bar-track">
                    @php $pct = $maxVol > 0 ? ($ex['volume'] / $maxVol) * 100 : 0; @endphp
                    <div class="volume-bar-fill" style="width: {{ $pct }}%"></div>
                </div>
            </div>
        @endforeach
    </div>
    
    <!-- Muscle Group Exercise History -->
    <div class="section-title">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <path d="M14.7 6.3a1 1 0 0 0 0 1.4l1.6 1.6a1 1 0 0 0 1.4 0l3.77-3.77a6 6 0 0 1-7.94 7.94l-6.91 6.91a2.12 2.12 0 0 1-3-3l6.91-6.91a6 6 0 0 1 7.94-7.94l-3.76 3.76z"/>
        </svg>
        Exercise History by Muscle Group
    </div>
    
    @foreach($muscleGroups as $key => $group)
        @if(!empty($group['exercises']))
            <div class="muscle-panel {{ $loop->first ? 'expanded' : '' }}">
                <div class="muscle-panel-header" onclick="togglePanel(this)">
                    <div class="muscle-panel-title">
                        <div class="muscle-panel-icon">
                            @if($key === 'chest')
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20 12V8h-4V4h-4v4H8V4H4v8h4v4h4v4h4v-4h4v-4z"/></svg>
                            @elseif($key === 'back')
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 2L2 7l10 5 10-5-10-5zM2 17l10 5 10-5M2 12l10 5 10-5"/></svg>
                            @elseif($key === 'legs')
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 3v18M8 21l4-4 4 4M8 3l4 4 4-4"/></svg>
                            @elseif($key === 'shoulders')
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="3"/><path d="M12 1v6m0 6v6m11-7h-6m-6 0H1"/></svg>
                            @elseif($key === 'arms')
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M8 6v12M16 6v12M4 10h16M4 14h16"/></svg>
                            @else
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="9"/><circle cx="12" cy="12" r="4"/></svg>
                            @endif
                        </div>
                        <div class="muscle-panel-info">
                            <h3>{{ $group['name'] }}</h3>
                            <p>{{ formatVolume($group['total_volume']) }} lbs total • {{ count($group['exercises']) }} exercises</p>
                        </div>
                    </div>
                    <svg class="muscle-panel-expand" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <polyline points="6 9 12 15 18 9"/>
                    </svg>
                </div>
                <div class="muscle-panel-body">
                    @foreach($group['exercises'] as $ex)
                        <div class="exercise-history-item">
                            <span class="exercise-history-name">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <path d="M6 9H4.5a2.5 2.5 0 0 1 0-5H6"/>
                                    <path d="M18 9h1.5a2.5 2.5 0 0 0 0-5H18"/>
                                    <path d="M4 22h16"/>
                                    <path d="M18 2H6v7a6 6 0 0 0 12 0V2Z"/>
                                </svg>
                                {{ $ex['exercise_name'] }}
                            </span>
                            <span class="exercise-history-sets">{{ $ex['total_sets'] }} sets</span>
                            <span class="exercise-history-volume">{{ formatVolume($ex['volume']) }} lbs</span>
                        </div>
                    @endforeach
                </div>
            </div>
        @endif
    @endforeach
@endif
@endsection

@section('scripts')
<script>
function togglePanel(header) {
    const panel = header.closest('.muscle-panel');
    panel.classList.toggle('expanded');
}
</script>
@endsection
