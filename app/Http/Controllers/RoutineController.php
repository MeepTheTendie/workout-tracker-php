<?php

namespace App\Http\Controllers;

use App\Models\Routine;
use App\Models\RoutineExercise;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class RoutineController extends Controller
{
    public function __construct()
    {
        // Middleware applied in routes
    }

    public function index()
    {
        return view('routines.index');
    }

    public function show(Routine $routine)
    {
        $this->authorize('view', $routine);
        return view('routines.show', compact('routine'));
    }

    public function store(Request $request)
    {
        $routine = Auth::user()->routines()->create([
            'name' => $request->name,
            'description' => $request->description,
            'created_at' => round(microtime(true) * 1000),
            'updated_at' => round(microtime(true) * 1000),
        ]);

        return response()->json(['id' => $routine->id]);
    }

    public function destroy(Routine $routine)
    {
        $this->authorize('delete', $routine);
        $routine->delete();
        return response()->json(['success' => true]);
    }

    public function addExercise(Request $request, Routine $routine)
    {
        $this->authorize('update', $routine);

        $count = $routine->exercises()->count();
        $routine->exercises()->create([
            'exercise_id' => $request->exercise_id,
            'order_index' => $count,
            'target_sets' => $request->target_sets,
            'target_reps' => $request->target_reps,
            'target_weight' => $request->target_weight,
            'created_at' => round(microtime(true) * 1000),
            'updated_at' => round(microtime(true) * 1000),
        ]);

        return response()->json(['success' => true]);
    }
}
