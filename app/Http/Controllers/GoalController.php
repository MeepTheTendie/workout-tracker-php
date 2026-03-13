<?php

namespace App\Http\Controllers;

use App\Models\Goal;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class GoalController extends Controller
{
    public function __construct()
    {
        // Middleware applied in routes
    }

    public function index()
    {
        return view('goals.index');
    }

    public function store(Request $request)
    {
        $goal = Auth::user()->goals()->create([
            'exercise_id' => $request->exercise_id,
            'target_weight' => $request->target_weight,
            'target_reps' => $request->target_reps ?? 1,
            'completed' => false,
            'created_at' => round(microtime(true) * 1000),
            'updated_at' => round(microtime(true) * 1000),
        ]);

        return response()->json(['id' => $goal->id]);
    }

    public function destroy(Goal $goal)
    {
        $this->authorize('delete', $goal);
        $goal->delete();
        return response()->json(['success' => true]);
    }

    public function complete(Goal $goal)
    {
        $this->authorize('update', $goal);
        $goal->update([
            'completed' => true,
            'updated_at' => round(microtime(true) * 1000),
        ]);
        return response()->json(['success' => true]);
    }
}
