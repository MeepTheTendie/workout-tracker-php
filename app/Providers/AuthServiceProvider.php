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
    /**
     * The model to policy mappings for the application.
     *
     * @var array<class-string, class-string>
     */
    protected $policies = [
        Workout::class => WorkoutPolicy::class,
        Goal::class => GoalPolicy::class,
        Routine::class => RoutinePolicy::class,
    ];

    /**
     * Register any authentication / authorization services.
     */
    public function boot(): void
    {
        //
    }
}
