@extends('layouts.app')

@section('title', 'My Goals')

@section('styles')
<style>
    .page-header-goals {
        display: flex;
        align-items: center;
        justify-content: space-between;
        margin-bottom: 20px;
        padding-bottom: 16px;
        border-bottom: 1px solid var(--border);
    }
    
    .page-header-goals-left {
        display: flex;
        align-items: center;
        gap: 12px;
    }
    
    .page-header-goals-left svg {
        width: 32px;
        height: 20px;
        fill: var(--accent);
    }
    
    .page-title-group h1 {
        font-size: 18px;
        font-weight: 700;
        letter-spacing: 1px;
        margin-bottom: 2px;
    }
    
    .page-title-group p {
        font-size: 11px;
        color: var(--text-dim);
        text-transform: uppercase;
        letter-spacing: 1px;
    }
    
    .suggestion-grid {
        display: grid;
        grid-template-columns: repeat(2, 1fr);
        gap: 12px;
        margin-bottom: 20px;
    }
    
    .suggestion-card {
        background: rgba(255,255,255,0.03);
        border: 1px dashed var(--border);
        border-radius: 10px;
        padding: 16px 12px;
        text-align: center;
        cursor: pointer;
        transition: all 0.2s;
    }
    
    .suggestion-card:hover {
        background: rgba(255,107,53,0.1);
        border-color: var(--accent);
        border-style: solid;
        transform: translateY(-2px);
    }
    
    .suggestion-card .icon {
        width: 28px;
        height: 28px;
        margin: 0 auto 10px;
        color: var(--accent);
    }
    
    .suggestion-card .title {
        font-size: 11px;
        font-weight: 700;
        text-transform: uppercase;
        color: var(--text);
        margin-bottom: 4px;
    }
    
    .suggestion-card .desc {
        font-size: 10px;
        color: var(--text-dim);
        line-height: 1.4;
    }
    
    .add-goal-btn {
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
        width: 100%;
        padding: 14px;
        background: var(--surface);
        border: 2px dashed var(--border);
        border-radius: 10px;
        color: var(--text);
        font-family: 'Space Mono', monospace;
        font-size: 12px;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 1px;
        cursor: pointer;
        transition: all 0.2s;
        margin-bottom: 20px;
    }
    
    .add-goal-btn:hover {
        border-color: var(--accent);
        color: var(--accent);
    }
    
    .add-goal-btn svg {
        width: 18px;
        height: 18px;
    }
    
    .goal-card {
        background: var(--surface);
        border: 1px solid var(--border);
        border-radius: 10px;
        padding: 16px;
        margin-bottom: 12px;
        transition: all 0.2s;
    }
    
    .goal-card:hover {
        border-color: var(--accent);
    }
    
    .goal-card.completed {
        opacity: 0.6;
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
    }
    
    .goal-exercise-icon {
        width: 28px;
        height: 28px;
        background: var(--bg);
        border-radius: 6px;
        display: flex;
        align-items: center;
        justify-content: center;
    }
    
    .goal-exercise-icon svg {
        width: 16px;
        height: 16px;
        color: var(--accent);
    }
    
    .goal-exercise-name {
        font-size: 13px;
        font-weight: 700;
        text-transform: uppercase;
    }
    
    .goal-actions {
        display: flex;
        gap: 8px;
    }
    
    .goal-action-btn {
        padding: 6px 10px;
        background: var(--bg);
        border: 1px solid var(--border);
        border-radius: 4px;
        font-family: 'Space Mono', monospace;
        font-size: 10px;
        color: var(--text-dim);
        cursor: pointer;
        transition: all 0.2s;
    }
    
    .goal-action-btn:hover {
        border-color: var(--accent);
        color: var(--accent);
    }
    
    .goal-action-btn.delete:hover {
        border-color: #ff6b6b;
        color: #ff6b6b;
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
    }
    
    .goal-progress-container {
        position: relative;
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
        transition: width 0.3s ease;
    }
    
    .goal-progress-fill.complete {
        background: var(--success);
    }
    
    .goal-progress-text {
        text-align: right;
        font-size: 11px;
        color: var(--text-dim);
        margin-top: 6px;
    }
    
    .complete-goal-btn {
        width: 100%;
        margin-top: 12px;
        padding: 10px;
        background: var(--success);
        border: none;
        border-radius: 6px;
        color: #000;
        font-family: 'Space Mono', monospace;
        font-size: 11px;
        font-weight: 700;
        text-transform: uppercase;
        cursor: pointer;
        transition: all 0.2s;
    }
    
    .complete-goal-btn:hover {
        filter: brightness(1.1);
    }
    
    .section-divider {
        display: flex;
        align-items: center;
        gap: 12px;
        margin: 24px 0 16px;
        font-size: 12px;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 1px;
        color: var(--text-dim);
    }
    
    .section-divider::before,
    .section-divider::after {
        content: '';
        flex: 1;
        height: 1px;
        background: var(--border);
    }
    
    /* Add Goal Form Modal */
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
        border-radius: 12px;
        padding: 24px;
        width: 100%;
        max-width: 400px;
        max-height: 90vh;
        overflow-y: auto;
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

<!-- Header with Branding -->
<div class="page-header-goals">
    <div class="page-header-goals-left">
        <svg viewBox="0 0 80 48" fill="none" xmlns="http://www.w3.org/2000/svg">
            <rect x="12" y="22" width="56" height="4"/>
            <rect x="4" y="14" width="4" height="20"/>
            <rect x="10" y="10" width="4" height="28"/>
            <rect x="16" y="16" width="4" height="16"/>
            <rect x="72" y="14" width="4" height="20"/>
            <rect x="66" y="10" width="4" height="28"/>
            <rect x="60" y="16" width="4" height="16"/>
        </svg>
        <div class="page-title-group">
            <h1>MY GOALS</h1>
            <p>Set targets. Track progress. Crush them.</p>
        </div>
    </div>
</div>

<!-- Suggestion Templates -->
@if($activeGoals->isEmpty())
    <div class="suggestion-grid">
        <div class="suggestion-card" onclick="prefillGoal('Bench Press', 225, 5)">
            <svg class="icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M6 9H4.5a2.5 2.5 0 0 1 0-5H6"/>
                <path d="M18 9h1.5a2.5 2.5 0 0 0 0-5H18"/>
                <path d="M4 22h16"/>
                <path d="M10 14.66V17c0 .55-.47.98-.97 1.21C7.85 18.75 7 20.24 7 22"/>
                <path d="M14 14.66V17c0 .55.47.98.97 1.21C16.15 18.75 17 20.24 17 22"/>
                <path d="M18 2H6v7a6 6 0 0 0 12 0V2Z"/>
            </svg>
            <div class="title">Bench PR</div>
            <div class="desc">+10% strength<br>target</div>
        </div>
        <div class="suggestion-card" onclick="prefillGoal('Squat', 315, 5)">
            <svg class="icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M12 3v18M8 21l4-4 4 4M8 3l4 4 4-4"/>
            </svg>
            <div class="title">Squat Goal</div>
            <div class="desc">3 plate<br>milestone</div>
        </div>
        <div class="suggestion-card" onclick="prefillGoal('Deadlift', 405, 3)">
            <svg class="icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M12 2L2 7l10 5 10-5-10-5zM2 17l10 5 10-5M2 12l10 5 10-5"/>
            </svg>
            <div class="title">Deadlift PR</div>
            <div class="desc">4 plate<br>club</div>
        </div>
        <div class="suggestion-card" onclick="prefillGoalVolume()">
            <svg class="icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <line x1="18" y1="20" x2="18" y2="10"/>
                <line x1="12" y1="20" x2="12" y2="4"/>
                <line x1="6" y1="20" x2="6" y2="14"/>
            </svg>
            <div class="title">Volume Goal</div>
            <div class="desc">50k lbs<br>weekly</div>
        </div>
    </div>
@endif

<!-- Add Goal Button -->
<button class="add-goal-btn" onclick="showAddGoalModal()">
    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
        <circle cx="12" cy="12" r="10"/>
        <line x1="12" y1="8" x2="12" y2="16"/>
        <line x1="8" y1="12" x2="16" y2="12"/>
    </svg>
    Add Goal
</button>

<!-- Active Goals -->
@if($activeGoals->isNotEmpty())
    @foreach($activeGoals as $goal)
        @php
            $currentWeight = $goal->current_weight;
            $progress = $goal->progress;
        @endphp
        <div class="goal-card">
            <div class="goal-card-header">
                <div class="goal-exercise">
                    <div class="goal-exercise-icon">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M6 9H4.5a2.5 2.5 0 0 1 0-5H6"/>
                            <path d="M18 9h1.5a2.5 2.5 0 0 0 0-5H18"/>
                            <path d="M4 22h16"/>
                            <path d="M18 2H6v7a6 6 0 0 0 12 0V2Z"/>
                        </svg>
                    </div>
                    <span class="goal-exercise-name">{{ $goal->exercise->name }}</span>
                </div>
                <div class="goal-actions">
                    <button class="goal-action-btn delete" onclick="deleteGoal({{ $goal->id }})">Delete</button>
                </div>
            </div>
            
            <div class="goal-stats">
                <span class="goal-stat-label">Current: <span class="goal-stat-value">{{ $currentWeight }} lbs</span></span>
                <span class="goal-stat-label">Target: <span class="goal-stat-value">{{ $goal->target_weight }} lbs</span></span>
            </div>
            
            <div class="goal-progress-container">
                <div class="goal-progress-bar">
                    <div class="goal-progress-fill {{ $progress >= 100 ? 'complete' : '' }}" style="width: {{ $progress }}%"></div>
                </div>
                <div class="goal-progress-text">{{ round($progress) }}%</div>
            </div>
            
            @if($progress >= 100)
                <button class="complete-goal-btn" onclick="completeGoal({{ $goal->id }})">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" style="vertical-align: middle; margin-right: 6px;">
                        <polyline points="20 6 9 17 4 12"/>
                    </svg>
                    Mark Complete!
                </button>
            @endif
        </div>
    @endforeach
@else
    <div class="empty" style="text-align: center; padding: 40px; color: var(--text-dim);">
        <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" style="margin-bottom: 16px; opacity: 0.3;">
            <circle cx="12" cy="12" r="10"/>
            <circle cx="12" cy="12" r="6"/>
            <circle cx="12" cy="12" r="2"/>
        </svg>
        <p>No active goals. Select a template above or create your own!</p>
    </div>
@endif

<!-- Completed Goals -->
@if($completedGoals->isNotEmpty())
    <div class="section-divider">Completed</div>
    
    @foreach($completedGoals as $goal)
        <div class="goal-card completed">
            <div class="goal-card-header">
                <div class="goal-exercise">
                    <div class="goal-exercise-icon" style="background: var(--success);">
                        <svg viewBox="0 0 24 24" fill="none" stroke="#000" stroke-width="3">
                            <polyline points="20 6 9 17 4 12"/>
                        </svg>
                    </div>
                    <span class="goal-exercise-name" style="text-decoration: line-through;">{{ $goal->exercise->name }}</span>
                </div>
                <div class="goal-actions">
                    <button class="goal-action-btn delete" onclick="deleteGoal({{ $goal->id }})">Delete</button>
                </div>
            </div>
            <div class="goal-stats">
                <span class="goal-stat-label">Achieved: <span class="goal-stat-value" style="color: var(--success);">{{ $goal->target_weight }} lbs × {{ $goal->target_reps }} reps</span></span>
            </div>
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
    // Find first exercise as placeholder for volume goal
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
