@extends('layouts.app')

@section('title', 'History')

@section('content')
<div class="page-header">
    <div class="page-title">Workout History</div>
</div>

@if($workouts->isEmpty())
    <div class="section empty">No workouts yet</div>
@else
    @foreach($workouts as $workout)
        <a href="/workouts/{{ $workout->id }}" style="text-decoration: none; color: inherit; display: block;">
            <div class="section">
                <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 12px;">
                    <div>
                        <div style="font-weight: 700;">{{ $workout->notes ?? 'Workout #' . $workout->id }}</div>
                        <div style="font-size: 12px; color: var(--text-dim);">{{ date('M d, Y', $workout->started_at / 1000) }}</div>
                    </div>
                    <div style="text-align: right;">
                        <div style="font-weight: 700;">{{ number_format($workout->volume) }}</div>
                        <div style="font-size: 12px; color: var(--text-dim);">lbs</div>
                    </div>
                </div>
                
                <div style="font-size: 12px; color: var(--text-dim); margin-bottom: 8px;">
                    {{ $workout->sets->count() }} sets · {{ $workout->duration ?? 0 }} min
                </div>
                
                @php
                    $exercises = $workout->sets->groupBy('exercise.name')->keys()->take(3);
                @endphp
                <div style="font-size: 12px;">
                    {{ $exercises->join(', ') }}{{ $workout->sets->groupBy('exercise_id')->count() > 3 ? '...' : '' }}
                </div>
            </div>
        </a>
    @endforeach
@endif
@endsection
