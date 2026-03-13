<?php

namespace App\Http\Controllers;

use App\Models\Workout;
use App\Models\WorkoutSet;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class StatsController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        
        $totalWorkouts = $user->workouts()->completed()->count();
        $totalVolume = Workout::where('user_id', $user->id)
            ->with('sets')
            ->get()
            ->sum(function ($workout) {
                return $workout->sets->sum(function ($set) {
                    return ($set->weight ?? 0) * ($set->reps ?? 0);
                });
            });
        
        $workoutsThisWeek = $user->workouts()
            ->completed()
            ->where('started_at', '>=', now()->startOfWeek()->timestamp * 1000)
            ->count();
        
        $avgDuration = Workout::where('user_id', $user->id)
            ->completed()
            ->get()
            ->avg('duration');
        
        return view('stats.index', compact('totalWorkouts', 'totalVolume', 'workoutsThisWeek', 'avgDuration'));
    }
}
