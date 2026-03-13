@extends('layouts.app')

@section('title', 'Dashboard')

@section('content')
<div class="page-header">
    <div class="page-title">Workout Tracker</div>
</div>

@php
$stats = auth()->user()->workouts()->completed()->get();
$totalWorkouts = $stats->count();
$totalVolume = $stats->sum('volume');
$recentWorkouts = auth()->user()->workouts()->completed()->with('sets.exercise')->latest('started_at')->take(5)->get();
@endphp

<div class="section">
    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px; margin-bottom: 16px;">
        <div>
            <div style="font-size: 12px; color: var(--text-dim);">WORKOUTS</div>
            <div style="font-size: 32px; font-weight: 700;">{{ $totalWorkouts }}</div>
        </div>
        <div>
            <div style="font-size: 12px; color: var(--text-dim);">TOTAL VOLUME</div>
            <div style="font-size: 32px; font-weight: 700;">{{ number_format($totalVolume / 1000) }}k</div>
        </div>
    </div>
</div>

<div style="display: grid; grid-template-columns: 1fr 1fr; gap: 12px; margin-bottom: 16px;">
    <a href="/goals" class="btn btn-secondary" style="text-decoration: none;">Goals</a>
    <a href="/routines" class="btn btn-secondary" style="text-decoration: none;">Routines</a>
</div>

<div class="section">
    <div style="font-size: 12px; font-weight: 700; text-transform: uppercase; margin-bottom: 16px;">Recent Workouts</div>
    
    @if($recentWorkouts->isEmpty())
        <div class="empty">No workouts yet</div>
    @else
        @foreach($recentWorkouts as $workout)
            <div style="padding: 12px; border: 2px solid var(--border); margin-bottom: 8px;">
                <div style="display: flex; justify-content: space-between; align-items: center;">
                    <div>
                        <div style="font-weight: 700;">WORKOUT #{{ $workout->id }} - {{ strtoupper(date('l', $workout->started_at / 1000)) }}</div>
                        <div style="font-size: 12px; color: var(--text-dim);">{{ date('M d, Y', $workout->started_at / 1000) }}</div>
                    </div>
                    <div style="text-align: right;">
                        <div style="font-size: 12px; color: var(--text-dim);">{{ $workout->sets->count() }} sets</div>
                        <div style="font-weight: 700;">{{ number_format($workout->volume / 1000, 1) }}k</div>
                    </div>
                </div>
            </div>
        @endforeach
    @endif
</div>
@endsection
