<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Goal extends Model
{
    use HasFactory;

    protected $fillable = ['user_id', 'exercise_id', 'target_weight', 'target_reps', 'deadline', 'completed', 'created_at', 'updated_at'];

    protected $casts = [
        'target_weight' => 'decimal:2',
        'completed' => 'boolean',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function exercise(): BelongsTo
    {
        return $this->belongsTo(Exercise::class);
    }

    public function getCurrentWeightAttribute(): float
    {
        return WorkoutSet::where('exercise_id', $this->exercise_id)
            ->whereHas('workout', function ($q) {
                $q->where('user_id', $this->user_id);
            })
            ->max('weight') ?? 0;
    }

    public function getProgressAttribute(): float
    {
        if ($this->target_weight <= 0) return 0;
        return min(($this->current_weight / $this->target_weight) * 100, 100);
    }
}
