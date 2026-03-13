@extends('layouts.app')

@section('title', 'PRs')

@section('content')
@php
$sets = App\Models\WorkoutSet::whereHas('workout', function($q) {
    $q->where('user_id', auth()->id());
})->with('exercise')->get();

$prs = [];
foreach ($sets->groupBy('exercise_id') as $exId => $exSets) {
    $exName = $exSets->first()->exercise->name ?? 'Unknown';
    $maxWeight = $exSets->max('weight') ?? 0;
    $maxReps = $exSets->max('reps') ?? 0;
    $maxVolume = $exSets->max(fn($s) => ($s->weight ?? 0) * ($s->reps ?? 0)) ?? 0;
    
    $prs[] = [
        'exercise_id' => $exId,
        'exercise_name' => $exName,
        'max_weight' => $maxWeight,
        'max_reps' => $maxReps,
        'max_volume' => $maxVolume
    ];
}

usort($prs, fn($a, $b) => $b['max_volume'] - $a['max_volume']);
@endphp

<div class="page-header">
    <div class="page-title">Personal Records</div>
</div>

<div class="section">
    <div style="font-size: 12px; font-weight: 700; text-transform: uppercase; margin-bottom: 16px;">Best Performance</div>
    
    @if(empty($prs))
        <div class="empty">No workout data yet</div>
    @else
        @foreach($prs as $pr)
            <div class="section" style="margin-bottom: 12px; padding: 16px;">
                <div style="font-weight: 700; font-size: 14px; margin-bottom: 12px;">
                    {{ strtoupper($pr['exercise_name']) }}
                </div>
                <div style="display: flex; gap: 16px; font-size: 12px;">
                    <div>
                        <div style="color: var(--text-dim);">Best Weight</div>
                        <div style="font-weight: 700;">{{ $pr['max_weight'] }} lbs</div>
                    </div>
                    <div>
                        <div style="color: var(--text-dim);">Best Reps</div>
                        <div style="font-weight: 700;">{{ $pr['max_reps'] }} reps</div>
                    </div>
                    <div>
                        <div style="color: var(--text-dim);">Best Set</div>
                        <div style="font-weight: 700;">{{ $pr['max_volume'] }} lbs</div>
                    </div>
                </div>
            </div>
        @endforeach
    @endif
</div>
@endsection
