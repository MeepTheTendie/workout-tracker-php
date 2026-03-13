<?php

namespace App\Http\Controllers;

use App\Http\Requests\AddExerciseRequest;
use App\Http\Requests\StoreRoutineRequest;
use App\Models\Routine;
use App\Models\RoutineExercise;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class RoutineController extends Controller
{
    public function index()
    {
        $routines = Auth::user()->routines()->with('exercises.exercise')->get();
        return view('routines.index', compact('routines'));
    }

    public function show(Routine $routine)
    {
        $this->authorize('view', $routine);
        $routine->load('exercises.exercise');
        return view('routines.show', compact('routine'));
    }

    public function store(StoreRoutineRequest $request)
    {
        $routine = Auth::user()->routines()->create($request->validated());

        return response()->json(['id' => $routine->id]);
    }

    public function destroy(Routine $routine)
    {
        $this->authorize('delete', $routine);
        $routine->delete();
        return response()->json(['success' => true]);
    }

    public function addExercise(AddExerciseRequest $request, Routine $routine)
    {
        $this->authorize('update', $routine);

        $count = $routine->exercises()->count();
        $routine->exercises()->create(array_merge($request->validated(), [
            'order_index' => $count,
        ]));

        return response()->json(['success' => true]);
    }
}
