<?php

namespace App\Policies;

use App\Models\Workout;
use App\Models\User;

class WorkoutPolicy
{
    public function view(User $user, Workout $workout): bool
    {
        return $user->id === $workout->user_id;
    }

    public function update(User $user, Workout $workout): bool
    {
        return $user->id === $workout->user_id;
    }

    public function delete(User $user, Workout $workout): bool
    {
        return $user->id === $workout->user_id;
    }
}
