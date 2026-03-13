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
        'started_at' => 'timestamp',
        'ended_at' => 'timestamp',
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
        $start = $this->started_at instanceof \Carbon\Carbon ? $this->started_at->timestamp : $this->started_at;
        $end = $this->ended_at instanceof \Carbon\Carbon ? $this->ended_at->timestamp : $this->ended_at;
        return (int) (($end - $start) / 60);
    }

    public function getStartedAtFormattedAttribute(): string
    {
        if (!$this->started_at) return '';
        $timestamp = $this->started_at instanceof \Carbon\Carbon ? $this->started_at->timestamp : $this->started_at;
        return date('M j, Y g:i A', $timestamp);
    }

    public function getEndedAtFormattedAttribute(): ?string
    {
        if (!$this->ended_at) return null;
        $timestamp = $this->ended_at instanceof \Carbon\Carbon ? $this->ended_at->timestamp : $this->ended_at;
        return date('M j, Y g:i A', $timestamp);
    }
}
