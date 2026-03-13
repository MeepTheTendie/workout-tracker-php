@extends('layouts.app')

@section('title', 'Workout')

@section('content')
@php
$sets = $workout->sets()->with('exercise')->orderBy('completed_at')->get();
$volume = $sets->sum(fn($s) => ($s->weight ?? 0) * ($s->reps ?? 0));

function formatDate($timestamp) {
    return date('l, M j, Y', $timestamp / 1000);
}

function formatDuration($started, $ended) {
    if (!$ended) return 'In progress';
    $mins = round(($ended - $started) / 60000);
    return "$mins minutes";
}

$grouped = [];
foreach ($sets as $set) {
    $name = $set->exercise->name;
    if (!isset($grouped[$name])) {
        $grouped[$name] = [];
    }
    $grouped[$name][] = $set;
}
@endphp

<div class="page-header">
    <div class="page-title">Workout</div>
    <a href="/workouts" class="btn" style="padding: 8px 16px;">← Back</a>
</div>

<div class="section">
    <div style="font-size: 18px; font-weight: 700; margin-bottom: 8px;">
        {{ formatDate($workout->started_at) }}
    </div>
    <div style="font-size: 12px; color: var(--text-dim); margin-bottom: 16px;">
        Duration: {{ formatDuration($workout->started_at, $workout->ended_at) }}
    </div>
    
    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px; padding: 16px; border: 2px solid var(--border);">
        <div style="text-align: center; border-right: 1px solid var(--border);">
            <div style="font-size: 28px; font-weight: 700;">{{ $sets->count() }}</div>
            <div style="font-size: 11px; color: var(--text-dim); text-transform: uppercase;">Sets</div>
        </div>
        <div style="text-align: center;">
            <div style="font-size: 28px; font-weight: 700;">{{ number_format($volume) }}</div>
            <div style="font-size: 11px; color: var(--text-dim); text-transform: uppercase;">Lbs Volume</div>
        </div>
    </div>
</div>

<div class="section">
    <div style="font-size: 12px; font-weight: 700; text-transform: uppercase; margin-bottom: 16px;">Exercises</div>
    
    @foreach($grouped as $exerciseName => $exSets)
        <div style="margin-bottom: 20px;">
            <div style="font-weight: 700; font-size: 14px; margin-bottom: 8px; text-transform: uppercase;">{{ $exerciseName }}</div>
            <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(80px, 1fr)); gap: 8px;">
                @foreach($exSets as $idx => $set)
                    <div style="padding: 12px; border: 2px solid var(--border); text-align: center;">
                        <div style="font-weight: 700; font-size: 16px;">{{ $set->weight ?? 0 }}×{{ $set->reps ?? 0 }}</div>
                        <div style="color: var(--text-dim); font-size: 10px;">set {{ $idx + 1 }}</div>
                    </div>
                @endforeach
            </div>
        </div>
    @endforeach
    
    @if(empty($grouped))
        <div class="empty">No exercises recorded</div>
    @endif
</div>

@if($workout->notes)
<div class="section">
    <div style="font-size: 12px; font-weight: 700; text-transform: uppercase; margin-bottom: 16px;">Notes</div>
    <div style="font-size: 14px;">{{ $workout->notes }}</div>
</div>
@endif
@endsection
