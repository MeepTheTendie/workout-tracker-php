<?php

use App\Http\Controllers\GoalController;
use App\Http\Controllers\PrsController;
use App\Http\Controllers\RoutineController;
use App\Http\Controllers\StatsController;
use App\Http\Controllers\WorkoutController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

// Public routes
Route::get('/', function () {
    return Auth::check() ? redirect()->route('dashboard') : redirect()->route('login');
});

// Auth routes
Route::get('/login', function () {
    return view('auth.login');
})->name('login');

Route::post('/login', function (\Illuminate\Http\Request $request) {
    $request->validate([
        'email' => 'required|email',
        'password' => 'required',
    ]);
    
    $credentials = $request->only('email', 'password');
    
    if (Auth::attempt($credentials)) {
        $request->session()->regenerate();
        return redirect()->route('dashboard');
    }
    
    return back()->withErrors(['email' => 'Invalid credentials']);
})->middleware('throttle:5,1');

Route::post('/logout', function () {
    Auth::logout();
    return redirect('/');
})->name('logout');

// Protected routes
Route::middleware('auth')->group(function () {
    Route::get('/dashboard', function () {
        return view('dashboard');
    })->name('dashboard');
    
    // Stats
    Route::get('/stats', [StatsController::class, 'index'])->name('stats.index');
    
    // PRs
    Route::get('/prs', [PrsController::class, 'index'])->name('prs.index');
    
    // Goals
    Route::get('/goals', [GoalController::class, 'index'])->name('goals.index');
    Route::post('/goals', [GoalController::class, 'store']);
    Route::delete('/goals/{goal}', [GoalController::class, 'destroy']);
    Route::post('/goals/{goal}/complete', [GoalController::class, 'complete']);
    
    // Routines
    Route::get('/routines', [RoutineController::class, 'index'])->name('routines.index');
    Route::get('/routines/{routine}', [RoutineController::class, 'show'])->name('routines.show');
    Route::post('/routines', [RoutineController::class, 'store']);
    Route::delete('/routines/{routine}', [RoutineController::class, 'destroy']);
    Route::post('/routines/{routine}/exercises', [RoutineController::class, 'addExercise']);
    
    // Workout routes
    Route::get('/workouts', [WorkoutController::class, 'index'])->name('workouts.index');
    Route::get('/workouts/create', [WorkoutController::class, 'create'])->name('workouts.create');
    Route::post('/workouts', [WorkoutController::class, 'store']);
    Route::get('/workouts/{workout}', [WorkoutController::class, 'show'])->name('workouts.show');
    Route::get('/workouts/{workout}/edit', [WorkoutController::class, 'edit'])->name('workouts.edit');
    Route::patch('/workouts/{workout}', [WorkoutController::class, 'update']);
    Route::delete('/workouts/{workout}', [WorkoutController::class, 'destroy']);
    
    // API routes for sets
    Route::post('/workouts/{workout}/sets', [WorkoutController::class, 'addSet']);
    Route::delete('/workouts/{workout}/sets/{set}', [WorkoutController::class, 'deleteSet']);
});
