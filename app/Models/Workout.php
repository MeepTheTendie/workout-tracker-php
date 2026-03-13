<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Workout extends Model
{
    use HasFactory;

    protected $fillable = ['user_id', 'started_at', 'ended_at', 'notes'];

    protected $casts = [
        'started_at' => 'integer',
        'ended_at' => 'integer',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function sets(): HasMany
    {
        return $this->hasMany(WorkoutSet::class);
    }

    public function scopeActive($query)
    {
        return $query->whereNull('ended_at');
    }

    public function scopeCompleted($query)
    {
        return $query->whereNotNull('ended_at');
    }

    public function getVolumeAttribute(): int
    {
        return $this->sets->sum(function ($set) {
            return ($set->weight ?? 0) * ($set->reps ?? 0);
        });
    }

    public function getDurationAttribute(): ?int
    {
        if (!$this->ended_at || !$this->started_at) {
            return null;
        }
        return (int) (($this->ended_at - $this->started_at) / 60000);
    }

    public function getStartedAtFormattedAttribute(): string
    {
        return $this->started_at ? date('M j, Y g:i A', $this->started_at / 1000) : '';
    }

    public function getEndedAtFormattedAttribute(): ?string
    {
        return $this->ended_at ? date('M j, Y g:i A', $this->ended_at / 1000) : null;
    }
}
