<?php

namespace App\Http\Controllers;

use App\Models\Exercise;
use App\Models\WorkoutSet;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PrsController extends Controller
{
    public function index()
    {
        $exercises = Exercise::orderBy('name')->get();
        
        $prs = [];
        foreach ($exercises as $exercise) {
            $pr = WorkoutSet::where('exercise_id', $exercise->id)
                ->whereHas('workout', function ($q) {
                    $q->where('user_id', Auth::id());
                })
                ->orderByDesc('weight')
                ->first();
            
            if ($pr) {
                $prs[$exercise->id] = [
                    'exercise' => $exercise,
                    'weight' => $pr->weight,
                    'reps' => $pr->reps,
                    'date' => $pr->completed_at,
                ];
            }
        }
        
        return view('prs.index', compact('prs'));
    }
}
