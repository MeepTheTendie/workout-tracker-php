<?php

namespace App\Http\Controllers;

use App\Http\Requests\AddSetRequest;
use App\Http\Requests\StoreWorkoutRequest;
use App\Models\Exercise;
use App\Models\Workout;
use App\Models\WorkoutSet;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class WorkoutController extends Controller
{
    public function index()
    {
        $workouts = Auth::user()->workouts()
            ->completed()
            ->with('sets.exercise')
            ->orderBy('started_at', 'desc')
            ->get();
            
        return view('workouts.index', compact('workouts'));
    }

    public function create()
    {
        $exercises = Exercise::orderBy('name')->get();
        $activeWorkout = Auth::user()->workouts()->active()->first();
        
        if ($activeWorkout) {
            return redirect()->route('workouts.edit', $activeWorkout);
        }
        
        return view('workouts.create', compact('exercises'));
    }

    public function store(StoreWorkoutRequest $request)
    {
        $workout = Auth::user()->workouts()->create([
            'started_at' => round(microtime(true) * 1000),
            'notes' => $request->notes,
        ]);
        
        return response()->json(['id' => $workout->id]);
    }

    public function show(Workout $workout)
    {
        $this->authorize('view', $workout);
        
        return view('workouts.show', compact('workout'));
    }

    public function edit(Workout $workout)
    {
        $this->authorize('view', $workout);
        
        $exercises = Exercise::orderBy('name')->get();
        $workout->load('sets.exercise');
        
        return view('workouts.edit', compact('workout', 'exercises'));
    }

    public function update(Request $request, Workout $workout)
    {
        $this->authorize('update', $workout);
        
        if ($request->has('ended_at')) {
            $workout->update(['ended_at' => round(microtime(true) * 1000)]);
        }
        
        if ($request->has('notes')) {
            $workout->update(['notes' => $request->notes]);
        }
        
        return response()->json(['success' => true]);
    }

    public function destroy(Workout $workout)
    {
        $this->authorize('delete', $workout);
        
        $workout->delete();
        
        return redirect()->route('workouts.index');
    }
    
    // API methods for AJAX
    public function addSet(AddSetRequest $request, Workout $workout)
    {
        $this->authorize('update', $workout);
        
        $set = $workout->sets()->create(array_merge($request->validated(), [
            'completed_at' => round(microtime(true) * 1000),
        ]));
        
        return response()->json(['id' => $set->id]);
    }
    
    public function deleteSet(Workout $workout, WorkoutSet $set)
    {
        $this->authorize('update', $workout);
        
        $set->delete();
        
        return response()->json(['success' => true]);
    }
}
