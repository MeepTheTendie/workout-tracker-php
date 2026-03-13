@extends('layouts.app')

@section('title', 'Stats')

@section('content')
@php
$workouts = auth()->user()->workouts()->completed()->with('sets')->get();
$sets = App\Models\WorkoutSet::whereHas('workout', function($q) {
    $q->where('user_id', auth()->id());
})->with('exercise')->get();

$totalWorkouts = $workouts->count();
$totalVolume = $sets->sum(fn($s) => ($s->weight ?? 0) * ($s->reps ?? 0));

$workoutsWithDuration = $workouts->filter(fn($w) => $w->ended_at && $w->started_at);
$avgDuration = $workoutsWithDuration->count() > 0 
    ? $workoutsWithDuration->avg(fn($w) => ($w->ended_at - $w->started_at) / 60000)
    : 0;

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

// Last 30 days
$thirtyDaysAgo = (time() - 30 * 86400) * 1000;
$recentWorkouts = $workouts->filter(fn($w) => $w->started_at >= $thirtyDaysAgo);
$recentWorkoutIds = $recentWorkouts->pluck('id');
$recentSets = $sets->filter(fn($s) => $recentWorkoutIds->contains($s->workout_id));
$recentVolume = $recentSets->sum(fn($s) => ($s->weight ?? 0) * ($s->reps ?? 0));

function formatVolume($vol) {
    if ($vol >= 1000000) return round($vol / 1000000, 1) . 'M';
    if ($vol >= 1000) return round($vol / 1000, 1) . 'k';
    return $vol;
}
@endphp

<div class="page-header">
    <div class="page-title">Stats</div>
</div>

<div style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 12px; margin-bottom: 24px;">
    <div class="section" style="text-align: center; padding: 16px;">
        <div style="font-size: 28px; font-weight: 700;">{{ $totalWorkouts }}</div>
        <div style="font-size: 11px; color: var(--text-dim); text-transform: uppercase;">Total Workouts</div>
    </div>
    <div class="section" style="text-align: center; padding: 16px;">
        <div style="font-size: 28px; font-weight: 700;">{{ formatVolume($totalVolume) }}</div>
        <div style="font-size: 11px; color: var(--text-dim); text-transform: uppercase;">Total Volume</div>
    </div>
    <div class="section" style="text-align: center; padding: 16px;">
        <div style="font-size: 28px; font-weight: 700;">{{ $streak }}</div>
        <div style="font-size: 11px; color: var(--text-dim); text-transform: uppercase;">Day Streak</div>
    </div>
</div>

<div class="section">
    <div style="font-size: 12px; font-weight: 700; text-transform: uppercase; margin-bottom: 16px;">Volume by Exercise</div>
    
    @if(empty($volumeByExercise))
        <div class="empty">No data yet</div>
    @else
        @foreach($volumeByExercise as $ex)
            <div style="margin-bottom: 16px;">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 6px;">
                    <span style="font-weight: 600; font-size: 13px;">{{ strtoupper($ex['exercise_name']) }}</span>
                    <span style="font-size: 12px; color: var(--text-dim);">{{ number_format($ex['volume']) }} lbs</span>
                </div>
                <div style="height: 8px; background: var(--bg); border-radius: 4px; overflow: hidden;">
                    @php $pct = $maxVol > 0 ? ($ex['volume'] / $maxVol) * 100 : 0; @endphp
                    <div style="height: 100%; width: {{ $pct }}%; background: var(--accent); border-radius: 4px;"></div>
                </div>
            </div>
        @endforeach
    @endif
</div>

<div class="section">
    <div style="font-size: 12px; font-weight: 700; text-transform: uppercase; margin-bottom: 16px;">Last 30 Days</div>
    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px;">
        <div style="text-align: center;">
            <div style="font-size: 32px; font-weight: 700;">{{ $recentWorkouts->count() }}</div>
            <div style="font-size: 11px; color: var(--text-dim); text-transform: uppercase;">Workouts</div>
        </div>
        <div style="text-align: center;">
            <div style="font-size: 32px; font-weight: 700;">{{ formatVolume($recentVolume) }}</div>
            <div style="font-size: 11px; color: var(--text-dim); text-transform: uppercase;">Volume</div>
        </div>
    </div>
</div>
@endsection
