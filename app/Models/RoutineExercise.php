<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RoutineExercise extends Model
{
    use HasFactory;

    protected $fillable = ['routine_id', 'exercise_id', 'order_index', 'target_sets', 'target_reps', 'target_weight', 'created_at', 'updated_at'];

    protected $casts = [
        'target_weight' => 'decimal:2',
        'created_at' => 'integer',
        'updated_at' => 'integer',
    ];

    public function routine(): BelongsTo
    {
        return $this->belongsTo(Routine::class);
    }

    public function exercise(): BelongsTo
    {
        return $this->belongsTo(Exercise::class);
    }
}
