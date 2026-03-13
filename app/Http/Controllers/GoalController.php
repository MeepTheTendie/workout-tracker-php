<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreGoalRequest;
use App\Models\Goal;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class GoalController extends Controller
{
    public function index()
    {
        $goals = Auth::user()->goals()->with('exercise')->get();
        return view('goals.index', compact('goals'));
    }

    public function store(StoreGoalRequest $request)
    {
        $goal = Auth::user()->goals()->create($request->validated());

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
        $goal->update(['completed' => true]);
        return response()->json(['success' => true]);
    }
}
