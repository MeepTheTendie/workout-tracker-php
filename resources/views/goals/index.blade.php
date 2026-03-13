@extends('layouts.app')

@section('title', 'Goals')

@section('content')
@php
$goals = auth()->user()->goals()->where('completed', false)->with('exercise')->get();
$completedGoals = auth()->user()->goals()->where('completed', true)->with('exercise')->orderBy('updated_at', 'desc')->get();
$exercises = App\Models\Exercise::orderBy('name')->get();
@endphp

<div class="page-header">
    <div class="page-title">PR Goals</div>
    <button class="btn" onclick="toggleAddForm()" id="addBtn">Add Goal</button>
</div>

<div id="addForm" class="section" style="display: none; background: var(--bg);">
    <form id="goalForm">
        <div class="form-group">
            <label class="form-label">Exercise</label>
            <select name="exercise_id" class="form-input" required>
                <option value="">Select exercise...</option>
                @foreach($exercises as $ex)
                    <option value="{{ $ex->id }}">{{ $ex->name }}</option>
                @endforeach
            </select>
        </div>
        
        <div style="display: flex; gap: 12px; margin-bottom: 16px;">
            <div class="form-group" style="flex: 1;">
                <label class="form-label">Target Weight (lbs)</label>
                <input type="number" name="target_weight" class="form-input" placeholder="0" step="0.5" required>
            </div>
            <div class="form-group" style="flex: 1;">
                <label class="form-label">Target Reps</label>
                <input type="number" name="target_reps" class="form-input" placeholder="1" value="1">
            </div>
        </div>
        
        <button type="submit" class="btn" style="width: 100%;">CREATE GOAL</button>
        <button type="button" class="btn btn-secondary" style="width: 100%; margin-top: 8px;" onclick="toggleAddForm()">CANCEL</button>
    </form>
</div>

<div class="section">
    <div style="font-size: 12px; font-weight: 700; text-transform: uppercase; margin-bottom: 16px;">Active Goals</div>
    
    @if($goals->isEmpty())
        <div class="empty">No active goals. Click "Add Goal" to create one.</div>
    @else
        @foreach($goals as $goal)
            @php
                $currentWeight = $goal->current_weight;
                $progress = $goal->progress;
            @endphp
            <div class="section" style="margin-bottom: 12px; position: relative;">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 8px;">
                    <div style="font-weight: 700; font-size: 14px;">{{ strtoupper($goal->exercise->name) }}</div>
                    <button class="btn btn-secondary" style="padding: 8px 12px; font-size: 10px;" onclick="deleteGoal({{ $goal->id }})">Delete</button>
                </div>
                
                <div style="display: flex; justify-content: space-between; font-size: 12px; margin-bottom: 8px;">
                    <span>Current: <strong>{{ $currentWeight }}</strong> lbs</span>
                    <span>Target: <strong>{{ $goal->target_weight }}</strong> lbs</span>
                </div>
                
                <div style="height: 8px; background: var(--bg); border-radius: 4px; overflow: hidden;">
                    <div style="height: 100%; width: {{ $progress }}%; background: {{ $progress >= 100 ? 'var(--accent)' : 'var(--border)' }}; border-radius: 4px;"></div>
                </div>
                
                @if($progress >= 100)
                    <button class="btn" style="margin-top: 12px; width: 100%;" onclick="completeGoal({{ $goal->id }})">Mark Complete!</button>
                @endif
            </div>
        @endforeach
    @endif
</div>

@if($completedGoals->isNotEmpty())
    <div class="section">
        <div style="font-size: 12px; font-weight: 700; text-transform: uppercase; margin-bottom: 16px;">Completed</div>
        
        @foreach($completedGoals as $goal)
            <div class="section" style="margin-bottom: 12px; opacity: 0.6;">
                <div style="display: flex; justify-content: space-between; align-items: center;">
                    <div>
                        <div style="font-weight: 700; font-size: 14px; text-decoration: line-through;">{{ strtoupper($goal->exercise->name) }}</div>
                        <div style="font-size: 11px; color: var(--text-dim);">Achieved: {{ $goal->target_weight }} lbs × {{ $goal->target_reps }} reps</div>
                    </div>
                    <button class="btn btn-secondary" style="padding: 8px 12px; font-size: 10px;" onclick="deleteGoal({{ $goal->id }})">Delete</button>
                </div>
            </div>
        @endforeach
    </div>
@endif

@endsection

@section('scripts')
<script>
function toggleAddForm() {
    const form = document.getElementById('addForm');
    const btn = document.getElementById('addBtn');
    if (form.style.display === 'none') {
        form.style.display = 'block';
        btn.style.display = 'none';
    } else {
        form.style.display = 'none';
        btn.style.display = 'block';
    }
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
