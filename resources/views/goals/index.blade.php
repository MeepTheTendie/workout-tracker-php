@extends('layouts.app')

@section('title', 'My Goals')

@section('styles')
<style>
    /* Page Header */
    .page-header-center {
        text-align: center;
        margin-bottom: 24px;
        padding-top: 20px;
    }
    
    .header-logo {
        width: 80px;
        height: 48px;
        margin: 0 auto 16px;
    }
    
    .header-logo svg {
        width: 100%;
        height: 100%;
    }
    
    .page-title {
        font-size: 24px;
        font-weight: 700;
        letter-spacing: 2px;
        color: var(--text);
    }
    
    /* White Card Container */
    .goals-container {
        background: #fff;
        border-radius: 4px;
        padding: 24px;
        margin-bottom: 20px;
    }
    
    .goals-container-title {
        background: #1a1a1a;
        color: #fff;
        padding: 12px;
        text-align: center;
        font-size: 12px;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 1px;
        margin: -24px -24px 20px -24px;
        border-radius: 4px 4px 0 0;
    }
    
    .goals-empty-text {
        text-align: center;
        font-size: 13px;
        color: #666;
        margin-bottom: 20px;
        line-height: 1.5;
    }
    
    /* Template Grid */
    .template-grid {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 12px;
    }
    
    .template-card {
        background: #f5f5f5;
        border: 1px solid #e0e0e0;
        border-radius: 4px;
        padding: 16px;
        cursor: pointer;
        transition: all 0.2s;
        position: relative;
    }
    
    .template-card:hover {
        border-color: var(--accent);
        transform: translateY(-2px);
    }
    
    .template-card-add {
        position: absolute;
        top: 8px;
        right: 8px;
        width: 20px;
        height: 20px;
        color: #999;
    }
    
    .template-card-title {
        font-size: 13px;
        font-weight: 700;
        color: #1a1a1a;
        margin-bottom: 4px;
        padding-right: 20px;
    }
    
    .template-card-desc {
        font-size: 11px;
        color: #666;
    }
    
    /* Active Goals */
    .section-title {
        font-size: 12px;
        color: var(--text-dim);
        text-transform: uppercase;
        letter-spacing: 2px;
        margin: 24px 0 16px;
    }
    
    .goal-card {
        background: var(--surface);
        border: 1px solid var(--border);
        border-radius: 8px;
        padding: 16px;
        margin-bottom: 12px;
    }
    
    .goal-card-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        margin-bottom: 12px;
    }
    
    .goal-exercise {
        display: flex;
        align-items: center;
        gap: 8px;
        font-size: 14px;
        font-weight: 700;
        color: var(--text);
    }
    
    .goal-exercise svg {
        width: 18px;
        height: 18px;
        color: var(--accent);
    }
    
    .goal-stats {
        display: flex;
        justify-content: space-between;
        font-size: 12px;
        margin-bottom: 10px;
    }
    
    .goal-stat-label {
        color: var(--text-dim);
    }
    
    .goal-stat-value {
        font-weight: 700;
        color: var(--text);
    }
    
    .goal-progress-bar {
        height: 8px;
        background: var(--bg);
        border-radius: 4px;
        overflow: hidden;
    }
    
    .goal-progress-fill {
        height: 100%;
        background: var(--accent);
        border-radius: 4px;
    }
    
    /* Add Goal Button */
    .add-goal-btn {
        width: 100%;
        padding: 14px;
        background: #1a1a1a;
        border: none;
        border-radius: 4px;
        color: #fff;
        font-family: 'Space Mono', monospace;
        font-size: 12px;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 1px;
        cursor: pointer;
        margin-bottom: 20px;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
    }
    
    .add-goal-btn svg {
        width: 16px;
        height: 16px;
    }
    
    /* Empty State */
    .empty-state {
        text-align: center;
        padding: 40px 20px;
        color: var(--text-dim);
    }
    
    /* Modal */
    .modal-overlay {
        display: none;
        position: fixed;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: rgba(0,0,0,0.8);
        z-index: 1000;
        align-items: center;
        justify-content: center;
        padding: 20px;
    }
    
    .modal-overlay.active {
        display: flex;
    }
    
    .modal-content {
        background: var(--surface);
        border: 1px solid var(--border);
        border-radius: 8px;
        padding: 24px;
        width: 100%;
        max-width: 400px;
    }
    
    .modal-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        margin-bottom: 20px;
    }
    
    .modal-title {
        font-size: 16px;
        font-weight: 700;
    }
    
    .modal-close {
        width: 32px;
        height: 32px;
        background: var(--bg);
        border: 1px solid var(--border);
        border-radius: 6px;
        color: var(--text);
        cursor: pointer;
        display: flex;
        align-items: center;
        justify-content: center;
    }
    
    .form-row {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 12px;
    }
</style>
@endsection

@section('content')
@php
$activeGoals = auth()->user()->goals()->where('completed', false)->with('exercise')->get();
$completedGoals = auth()->user()->goals()->where('completed', true)->with('exercise')->orderBy('updated_at', 'desc')->get();
$exercises = App\Models\Exercise::orderBy('name')->get();
@endphp

<!-- Header -->
<div class="page-header-center">
    <div class="header-logo">
        <svg viewBox="0 0 100 60" fill="none" xmlns="http://www.w3.org/2000/svg">
            <rect x="20" y="26" width="60" height="8" fill="#e0e0e0"/>
            <rect x="4" y="14" width="8" height="32" fill="#c0c0c0"/>
            <rect x="14" y="8" width="6" height="44" fill="#d0d0d0"/>
            <rect x="22" y="18" width="6" height="24" fill="#b0b0b0"/>
            <rect x="88" y="14" width="8" height="32" fill="#c0c0c0"/>
            <rect x="80" y="8" width="6" height="44" fill="#d0d0d0"/>
            <rect x="72" y="18" width="6" height="24" fill="#b0b0b0"/>
            <rect x="42" y="24" width="16" height="12" fill="#a0a0a0"/>
        </svg>
    </div>
    <h1 class="page-title">MY GOALS</h1>
</div>

<!-- Add Goal Button with Plus Icon -->
<button class="add-goal-btn" onclick="showAddGoalModal()">
    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
        <line x1="12" y1="5" x2="12" y2="19"/>
        <line x1="5" y1="12" x2="19" y2="12"/>
    </svg>
    ADD GOAL
</button>

<!-- Goals Container -->
<div class="goals-container">
    <div class="goals-container-title">ADD GOAL</div>
    
    @if($activeGoals->isEmpty())
        <p class="goals-empty-text">No active goals. Select a template to start, or create a custom one.</p>
        
        <div class="template-grid">
            <div class="template-card" onclick="prefillGoal('Bench Press', 225, 5)">
                <svg class="template-card-add" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <line x1="12" y1="5" x2="12" y2="19"/>
                    <line x1="5" y1="12" x2="19" y2="12"/>
                </svg>
                <div class="template-card-title">Increase Bench PR (10%)</div>
            </div>
            <div class="template-card" onclick="prefillGoalVolume()">
                <svg class="template-card-add" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <line x1="12" y1="5" x2="12" y2="19"/>
                    <line x1="5" y1="12" x2="19" y2="12"/>
                </svg>
                <div class="template-card-title">Total Volume Goal (50k lbs)</div>
            </div>
            <div class="template-card" onclick="prefillGoal('Body Weight', 180, 1)">
                <svg class="template-card-add" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <line x1="12" y1="5" x2="12" y2="19"/>
                    <line x1="5" y1="12" x2="19" y2="12"/>
                </svg>
                <div class="template-card-title">Lose Body Fat (5 lbs)</div>
            </div>
            <div class="template-card" onclick="prefillGoal('Workout Streak', 7, 1)">
                <svg class="template-card-add" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <line x1="12" y1="5" x2="12" y2="19"/>
                    <line x1="5" y1="12" x2="19" y2="12"/>
                </svg>
                <div class="template-card-title">New Workout Streak (7 Days)</div>
            </div>
        </div>
    @else
        <!-- Show active goals inside the white container -->
        @foreach($activeGoals as $goal)
            @php
                $currentWeight = $goal->current_weight;
                $progress = $goal->progress;
            @endphp
            <div class="goal-card" style="background: #f5f5f5; border: 1px solid #e0e0e0;">
                <div class="goal-card-header">
                    <span class="goal-exercise" style="color: #1a1a1a;">
                        <!-- Target Icon -->
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <circle cx="12" cy="12" r="10"/>
                            <circle cx="12" cy="12" r="6"/>
                            <circle cx="12" cy="12" r="2"/>
                        </svg>
                        {{ $goal->exercise->name }}
                    </span>
                </div>
                <div class="goal-stats">
                    <span class="goal-stat-label" style="color: #666;">Current: <span class="goal-stat-value" style="color: #1a1a1a;">{{ $currentWeight }} lbs</span></span>
                    <span class="goal-stat-label" style="color: #666;">Target: <span class="goal-stat-value" style="color: #1a1a1a;">{{ $goal->target_weight }} lbs</span></span>
                </div>
                <div class="goal-progress-bar" style="background: #e0e0e0;">
                    <div class="goal-progress-fill" style="width: {{ $progress }}%"></div>
                </div>
            </div>
        @endforeach
    @endif
</div>

<!-- Active Goals (outside container if we have templates above) -->
@if(!$activeGoals->isEmpty())
    <p class="section-title">ACTIVE GOALS</p>
    @foreach($activeGoals as $goal)
        @php
            $currentWeight = $goal->current_weight;
            $progress = $goal->progress;
        @endphp
        <div class="goal-card">
            <div class="goal-card-header">
                <span class="goal-exercise">
                    <!-- Target Icon -->
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <circle cx="12" cy="12" r="10"/>
                        <circle cx="12" cy="12" r="6"/>
                        <circle cx="12" cy="12" r="2"/>
                    </svg>
                    {{ $goal->exercise->name }}
                </span>
                <button class="icon-btn" onclick="deleteGoal({{ $goal->id }})" style="padding: 4px 8px; font-size: 10px;">Delete</button>
            </div>
            <div class="goal-stats">
                <span class="goal-stat-label">Current: <span class="goal-stat-value">{{ $currentWeight }} lbs</span></span>
                <span class="goal-stat-label">Target: <span class="goal-stat-value">{{ $goal->target_weight }} lbs</span></span>
            </div>
            <div class="goal-progress-bar">
                <div class="goal-progress-fill" style="width: {{ $progress }}%"></div>
            </div>
            @if($progress >= 100)
                <button class="btn btn-full" style="margin-top: 12px;" onclick="completeGoal({{ $goal->id }})">Mark Complete!</button>
            @endif
        </div>
    @endforeach
@endif

<!-- Add Goal Modal -->
<div id="addGoalModal" class="modal-overlay">
    <div class="modal-content">
        <div class="modal-header">
            <h3 class="modal-title">Add New Goal</h3>
            <button class="modal-close" onclick="hideAddGoalModal()">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <line x1="18" y1="6" x2="6" y2="18"/>
                    <line x1="6" y1="6" x2="18" y2="18"/>
                </svg>
            </button>
        </div>
        
        <form id="goalForm">
            <div class="form-group">
                <label class="form-label">Exercise</label>
                <select name="exercise_id" class="form-input" id="goalExercise" required>
                    <option value="">Select exercise...</option>
                    @foreach($exercises as $ex)
                        <option value="{{ $ex->id }}">{{ $ex->name }}</option>
                    @endforeach
                </select>
            </div>
            
            <div class="form-row">
                <div class="form-group">
                    <label class="form-label">Target Weight (lbs)</label>
                    <input type="number" name="target_weight" class="form-input" id="goalWeight" placeholder="0" step="0.5" required>
                </div>
                <div class="form-group">
                    <label class="form-label">Target Reps</label>
                    <input type="number" name="target_reps" class="form-input" id="goalReps" placeholder="1" value="1">
                </div>
            </div>
            
            <button type="submit" class="btn btn-full">Create Goal</button>
            <button type="button" class="btn btn-secondary btn-full" style="margin-top: 8px;" onclick="hideAddGoalModal()">Cancel</button>
        </form>
    </div>
</div>
@endsection

@section('scripts')
<script>
function showAddGoalModal() {
    document.getElementById('addGoalModal').classList.add('active');
}

function hideAddGoalModal() {
    document.getElementById('addGoalModal').classList.remove('active');
}

function prefillGoal(exerciseName, weight, reps) {
    const select = document.getElementById('goalExercise');
    for (let option of select.options) {
        if (option.text === exerciseName) {
            select.value = option.value;
            break;
        }
    }
    document.getElementById('goalWeight').value = weight;
    document.getElementById('goalReps').value = reps;
    showAddGoalModal();
}

function prefillGoalVolume() {
    showAddGoalModal();
}

document.getElementById('goalForm').addEventListener('submit', async function(e) {
    e.preventDefault();
    const formData = new FormData(e.target);
    const data = {
        exercise_id: formData.get('exercise_id'),
        target_weight: parseFloat(formData.get('target_weight')),
        target_reps: parseInt(formData.get('target_reps')) || 1
    };
    
    await fetch('/goals', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
        },
        body: JSON.stringify(data)
    });
    
    location.reload();
});

async function deleteGoal(id) {
    if (!confirm('Delete this goal?')) return;
    await fetch(`/goals/${id}`, {
        method: 'DELETE',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
        }
    });
    location.reload();
}

async function completeGoal(id) {
    await fetch(`/goals/${id}/complete`, {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
        }
    });
    location.reload();
}
</script>
@endsection
