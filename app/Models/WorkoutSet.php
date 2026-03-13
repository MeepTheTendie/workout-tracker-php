<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WorkoutSet extends Model
{
    use HasFactory;

    protected $fillable = ['workout_id', 'exercise_id', 'set_number', 'reps', 'weight', 'completed_at'];

    protected $casts = [
        'completed_at' => 'integer',
        'weight' => 'decimal:2',
    ];

    public function workout(): BelongsTo
    {
        return $this->belongsTo(Workout::class);
    }

    public function exercise(): BelongsTo
    {
        return $this->belongsTo(Exercise::class);
    }

    public function getVolumeAttribute(): int
    {
        return ($this->weight ?? 0) * ($this->reps ?? 0);
    }

    public function getCompletedAtFormattedAttribute(): string
    {
        return $this->completed_at ? date('g:i A', $this->completed_at / 1000) : '';
    }
}
