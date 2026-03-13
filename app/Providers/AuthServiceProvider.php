<?php

namespace App\Providers;

use App\Models\Goal;
use App\Models\Routine;
use App\Models\Workout;
use App\Policies\GoalPolicy;
use App\Policies\RoutinePolicy;
use App\Policies\WorkoutPolicy;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;

class AuthServiceProvider extends ServiceProvider
{
    protected $policies = [
        Workout::class => WorkoutPolicy::class,
        Goal::class => GoalPolicy::class,
        Routine::class => RoutinePolicy::class,
    ];

    public function boot(): void
    {
        //
    }
}
