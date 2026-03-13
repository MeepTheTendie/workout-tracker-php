@extends('layouts.app')

@section('title', 'Personal Records')

@section('styles')
<style>
    .page-header-prs {
        display: flex;
        align-items: center;
        gap: 12px;
        margin-bottom: 20px;
        padding-bottom: 16px;
        border-bottom: 1px solid var(--border);
    }
    
    .page-header-prs svg {
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
    
    /* Filter Icon */
    .filter-icon-btn {
        margin-left: auto;
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
    
    .filter-icon-btn:hover {
        border-color: var(--accent);
        color: var(--accent);
    }
    
    .filter-icon-btn svg {
        width: 18px;
        height: 18px;
    }
    
    /* Recent PR Spotlight */
    .pr-spotlight {
        background: linear-gradient(135deg, var(--surface) 0%, rgba(255,107,53,0.15) 100%);
        border: 1px solid var(--accent);
        border-radius: 12px;
        padding: 20px;
        margin-bottom: 20px;
        text-align: center;
    }
    
    .pr-spotlight-label {
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 6px;
        font-size: 11px;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 1px;
        color: var(--accent);
        margin-bottom: 12px;
    }
    
    .pr-spotlift-icon {
        width: 48px;
        height: 48px;
        margin: 0 auto 12px;
        background: var(--accent);
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
    }
    
    .pr-spotlift-icon svg {
        width: 24px;
        height: 24px;
        color: #fff;
    }
    
    .pr-spotlight-exercise {
        font-size: 14px;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 1px;
        margin-bottom: 8px;
    }
    
    .pr-spotlight-value {
        font-size: 36px;
        font-weight: 700;
        color: var(--accent);
        margin-bottom: 4px;
    }
    
    .pr-spotlight-detail {
        font-size: 13px;
        color: var(--text-dim);
    }
    
    .pr-spotlight-date {
        font-size: 11px;
        color: var(--text-muted);
        margin-top: 8px;
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
    
    .muscle-panel-name {
        font-size: 14px;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }
    
    .muscle-panel-count {
        font-size: 11px;
        color: var(--text-dim);
        background: var(--bg);
        padding: 4px 10px;
        border-radius: 12px;
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
    
    /* PR Cards */
    .pr-card {
        display: flex;
        align-items: center;
        gap: 16px;
        padding: 14px 16px;
        border-bottom: 1px solid var(--border);
        transition: background 0.2s;
    }
    
    .pr-card:hover {
        background: var(--surface-hover);
    }
    
    .pr-card:last-child {
        border-bottom: none;
    }
    
    .pr-rank {
        display: flex;
        align-items: center;
        justify-content: center;
        width: 32px;
        height: 32px;
        background: var(--bg);
        border-radius: 8px;
        font-size: 12px;
        font-weight: 700;
    }
    
    .pr-rank.gold {
        background: linear-gradient(135deg, #ffd700, #ffaa00);
        color: #000;
    }
    
    .pr-rank.silver {
        background: linear-gradient(135deg, #c0c0c0, #a0a0a0);
        color: #000;
    }
    
    .pr-rank.bronze {
        background: linear-gradient(135deg, #cd7f32, #b87333);
        color: #000;
    }
    
    .pr-info {
        flex: 1;
    }
    
    .pr-exercise {
        font-size: 13px;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        margin-bottom: 4px;
    }
    
    .pr-progress {
        display: flex;
        align-items: center;
        gap: 8px;
    }
    
    .pr-progress-bar {
        flex: 1;
        height: 6px;
        background: var(--bg);
        border-radius: 3px;
        overflow: hidden;
    }
    
    .pr-progress-fill {
        height: 100%;
        background: var(--accent);
        border-radius: 3px;
        transition: width 0.3s;
    }
    
    .pr-progress-text {
        font-size: 10px;
        color: var(--text-dim);
        min-width: 36px;
        text-align: right;
    }
    
    .pr-stats {
        text-align: right;
    }
    
    .pr-weight {
        font-size: 18px;
        font-weight: 700;
        color: var(--accent);
    }
    
    .pr-reps {
        font-size: 11px;
        color: var(--text-dim);
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
</style>
@endsection

@section('content')
@php
$sets = App\Models\WorkoutSet::whereHas('workout', function($q) {
    $q->where('user_id', auth()->id());
})->with('exercise')->get();

// Group PRs by muscle group
$muscleGroups = [
    'chest' => ['name' => 'Chest', 'icon' => 'chest', 'prs' => []],
    'back' => ['name' => 'Back', 'icon' => 'back', 'prs' => []],
    'legs' => ['name' => 'Legs', 'icon' => 'legs', 'prs' => []],
    'shoulders' => ['name' => 'Shoulders', 'icon' => 'shoulders', 'prs' => []],
    'arms' => ['name' => 'Arms', 'icon' => 'arms', 'prs' => []],
    'core' => ['name' => 'Core', 'icon' => 'core', 'prs' => []],
];

$allPrs = [];

foreach ($sets->groupBy('exercise_id') as $exId => $exSets) {
    $exName = $exSets->first()->exercise->name ?? 'Unknown';
    $maxWeight = $exSets->max('weight') ?? 0;
    $maxReps = $exSets->max('reps') ?? 0;
    $maxVolume = $exSets->max(fn($s) => ($s->weight ?? 0) * ($s->reps ?? 0)) ?? 0;
    
    $pr = [
        'exercise_id' => $exId,
        'exercise_name' => $exName,
        'max_weight' => $maxWeight,
        'max_reps' => $maxReps,
        'max_volume' => $maxVolume,
    ];
    
    $allPrs[] = $pr;
    
    // Categorize by muscle group
    $nameLower = strtolower($exName);
    if (str_contains($nameLower, 'press') || str_contains($nameLower, 'fly') || str_contains($nameLower, 'chest')) {
        $muscleGroups['chest']['prs'][] = $pr;
    } elseif (str_contains($nameLower, 'row') || str_contains($nameLower, 'pull') || str_contains($nameLower, 'lat') || str_contains($nameLower, 'deadlift')) {
        $muscleGroups['back']['prs'][] = $pr;
    } elseif (str_contains($nameLower, 'squat') || str_contains($nameLower, 'leg') || str_contains($nameLower, 'calf')) {
        $muscleGroups['legs']['prs'][] = $pr;
    } elseif (str_contains($nameLower, 'shoulder') || str_contains($nameLower, 'raise')) {
        $muscleGroups['shoulders']['prs'][] = $pr;
    } elseif (str_contains($nameLower, 'curl') || str_contains($nameLower, 'bicep') || str_contains($nameLower, 'tricep')) {
        $muscleGroups['arms']['prs'][] = $pr;
    } elseif (str_contains($nameLower, 'crunch') || str_contains($nameLower, 'plank') || str_contains($nameLower, 'torso')) {
        $muscleGroups['core']['prs'][] = $pr;
    } else {
        $muscleGroups['chest']['prs'][] = $pr; // Default
    }
}

// Sort all PRs by max volume to find top
usort($allPrs, fn($a, $b) => $b['max_volume'] - $a['max_volume']);
$topPr = $allPrs[0] ?? null;

// Calculate max volume for progress bars
$maxPrVolume = !empty($allPrs) ? max(array_column($allPrs, 'max_volume')) : 1;
@endphp

<!-- Header with Branding -->
<div class="page-header-prs">
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
        <h1>PERSONAL RECORDS</h1>
        <p>Your best lifts, all in one place.</p>
    </div>
    <button class="filter-icon-btn" onclick="toggleFilters()">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <polygon points="22 3 2 3 10 12.46 10 19 14 21 14 12.46 22 3"/>
        </svg>
    </button>
</div>

@if(empty($allPrs))
    <div class="empty-state">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
            <path d="M6 9H4.5a2.5 2.5 0 0 1 0-5H6"/>
            <path d="M18 9h1.5a2.5 2.5 0 0 0 0-5H18"/>
            <path d="M4 22h16"/>
            <path d="M10 14.66V17c0 .55-.47.98-.97 1.21C7.85 18.75 7 20.24 7 22"/>
            <path d="M14 14.66V17c0 .55.47.98.97 1.21C16.15 18.75 17 20.24 17 22"/>
            <path d="M18 2H6v7a6 6 0 0 0 12 0V2Z"/>
        </svg>
        <h3>No PRs Yet</h3>
        <p>Log some workouts to start tracking your personal records.</p>
        <a href="/workouts/create" class="btn">Start Workout</a>
    </div>
@else
    @if($topPr)
        <!-- Recent PR Spotlight -->
        <div class="pr-spotlight">
            <div class="pr-spotlight-label">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/>
                </svg>
                Top Personal Record
            </div>
            <div class="pr-spotlift-icon">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M6 9H4.5a2.5 2.5 0 0 1 0-5H6"/>
                    <path d="M18 9h1.5a2.5 2.5 0 0 0 0-5H18"/>
                    <path d="M4 22h16"/>
                    <path d="M10 14.66V17c0 .55-.47.98-.97 1.21C7.85 18.75 7 20.24 7 22"/>
                    <path d="M14 14.66V17c0 .55.47.98.97 1.21C16.15 18.75 17 20.24 17 22"/>
                    <path d="M18 2H6v7a6 6 0 0 0 12 0V2Z"/>
                </svg>
            </div>
            <div class="pr-spotlight-exercise">{{ $topPr['exercise_name'] }}</div>
            <div class="pr-spotlight-value">{{ $topPr['max_weight'] }} <span style="font-size: 18px;">lbs</span></div>
            <div class="pr-spotlight-detail">{{ $topPr['max_reps'] }} reps • {{ number_format($topPr['max_volume']) }} lbs total</div>
        </div>
    @endif
    
    <!-- Muscle Group Panels -->
    @foreach($muscleGroups as $key => $group)
        @if(!empty($group['prs']))
            @php
                usort($group['prs'], fn($a, $b) => $b['max_volume'] - $a['max_volume']);
            @endphp
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
                        <span class="muscle-panel-name">{{ $group['name'] }}</span>
                    </div>
                    <div style="display: flex; align-items: center; gap: 12px;">
                        <span class="muscle-panel-count">{{ count($group['prs']) }} exercises</span>
                        <svg class="muscle-panel-expand" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <polyline points="6 9 12 15 18 9"/>
                        </svg>
                    </div>
                </div>
                <div class="muscle-panel-body">
                    @foreach($group['prs'] as $idx => $pr)
                        <div class="pr-card">
                            <div class="pr-rank {{ $idx === 0 ? 'gold' : ($idx === 1 ? 'silver' : ($idx === 2 ? 'bronze' : '')) }}">
                                {{ $idx + 1 }}
                            </div>
                            <div class="pr-info">
                                <div class="pr-exercise">{{ $pr['exercise_name'] }}</div>
                                <div class="pr-progress">
                                    <div class="pr-progress-bar">
                                        <div class="pr-progress-fill" style="width: {{ min(100, ($pr['max_volume'] / $maxPrVolume) * 100) }}%"></div>
                                    </div>
                                    <span class="pr-progress-text">{{ round(($pr['max_volume'] / $maxPrVolume) * 100) }}%</span>
                                </div>
                            </div>
                            <div class="pr-stats">
                                <div class="pr-weight">{{ $pr['max_weight'] }}</div>
                                <div class="pr-reps">{{ $pr['max_reps'] }} reps</div>
                            </div>
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

function toggleFilters() {
    // Filter functionality would go here
    alert('Filter options coming soon!');
}
</script>
@endsection
