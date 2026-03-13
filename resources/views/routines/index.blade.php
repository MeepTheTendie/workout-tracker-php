@extends('layouts.app')

@section('title', 'My Routines')

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
    
    /* New Button */
    .new-btn {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 10px 20px;
        background: #333;
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
    }
    
    .new-btn svg {
        width: 14px;
        height: 14px;
    }
    
    /* White Card Container */
    .routines-container {
        background: #fff;
        border-radius: 4px;
        padding: 24px;
        margin-bottom: 20px;
    }
    
    .routines-empty-text {
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
        line-height: 1.4;
    }
    
    /* Routine Cards */
    .section-title {
        font-size: 12px;
        color: var(--text-dim);
        text-transform: uppercase;
        letter-spacing: 2px;
        margin: 24px 0 16px;
    }
    
    .routine-card {
        background: var(--surface);
        border: 1px solid var(--border);
        border-radius: 8px;
        overflow: hidden;
        margin-bottom: 12px;
    }
    
    .routine-card-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 16px;
        cursor: pointer;
    }
    
    .routine-card-info {
        display: flex;
        align-items: center;
        gap: 12px;
    }
    
    .routine-icon {
        width: 40px;
        height: 40px;
        background: var(--bg);
        border-radius: 8px;
        display: flex;
        align-items: center;
        justify-content: center;
    }
    
    .routine-icon svg {
        width: 20px;
        height: 20px;
        color: var(--accent);
    }
    
    .routine-name {
        font-size: 14px;
        font-weight: 700;
        text-transform: uppercase;
    }
    
    .routine-meta {
        font-size: 11px;
        color: var(--text-dim);
    }
    
    .routine-card-body {
        display: none;
        padding: 0 16px 16px;
        border-top: 1px solid var(--border);
    }
    
    .routine-card.expanded .routine-card-body {
        display: block;
    }
    
    .routine-card.expanded .routine-expand-icon {
        transform: rotate(180deg);
    }
    
    .routine-exercises {
        margin: 12px 0;
    }
    
    .routine-exercise-item {
        display: flex;
        align-items: center;
        gap: 10px;
        padding: 8px 0;
        border-bottom: 1px solid var(--border);
        font-size: 12px;
    }
    
    .routine-exercise-item:last-child {
        border-bottom: none;
    }
    
    .routine-actions {
        display: flex;
        gap: 8px;
    }
    
    .routine-action-btn {
        flex: 1;
        padding: 10px;
        background: var(--bg);
        border: 1px solid var(--border);
        border-radius: 6px;
        font-family: 'Space Mono', monospace;
        font-size: 11px;
        font-weight: 700;
        text-transform: uppercase;
        color: var(--text);
        cursor: pointer;
        text-decoration: none;
        text-align: center;
    }
    
    .routine-action-btn.primary {
        background: var(--accent);
        border-color: var(--accent);
        color: #fff;
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
    }
</style>
@endsection

@section('content')
@php
$routines = auth()->user()->routines()->with('exercises.exercise')->get();
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
    <h1 class="page-title">MY ROUTINES</h1>
</div>

<div style="text-align: center;">
    <button class="new-btn" onclick="showAddRoutineModal()">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <line x1="12" y1="5" x2="12" y2="19"/>
            <line x1="5" y1="12" x2="19" y2="12"/>
        </svg>
        NEW
    </button>
</div>

<!-- Routines Container -->
<div class="routines-container">
    @if($routines->isEmpty())
        <p class="routines-empty-text">No routines yet. Select a template to start, or create a custom one.</p>
        
        <div class="template-grid">
            <div class="template-card" onclick="createTemplateRoutine('Full Body: 3-Day Split (Strength)', 'Compound movements for full body strength', ['Squat', 'Bench Press', 'Barbell Row', 'Overhead Press'])">
                <svg class="template-card-add" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <line x1="12" y1="5" x2="12" y2="19"/>
                    <line x1="5" y1="12" x2="19" y2="12"/>
                </svg>
                <div class="template-card-title">Full Body: 3-Day Split (Strength)</div>
            </div>
            <div class="template-card" onclick="createTemplateRoutine('Lower Body Focus (Leg Day)', 'Quads, hamstrings, and calves', ['Squat', 'Leg Press', 'Leg Curl', 'Calf Raise'])">
                <svg class="template-card-add" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <line x1="12" y1="5" x2="12" y2="19"/>
                    <line x1="5" y1="12" x2="19" y2="12"/>
                </svg>
                <div class="template-card-title">Lower Body Focus (Leg Day)</div>
            </div>
            <div class="template-card" onclick="createTemplateRoutine('Cardio & Conditioning (HIIT)', 'High intensity interval training', ['Treadmill Run', 'Burpees', 'Mountain Climbers', 'Jump Rope'])">
                <svg class="template-card-add" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <line x1="12" y1="5" x2="12" y2="19"/>
                    <line x1="5" y1="12" x2="19" y2="12"/>
                </svg>
                <div class="template-card-title">Cardio & Conditioning (HIIT)</div>
            </div>
            <div class="template-card" onclick="createTemplateRoutine('Upper Body Power (Push/Pull)', 'Chest, back, shoulders, and arms', ['Bench Press', 'Lat Pulldown', 'Shoulder Press', 'Bicep Curl'])">
                <svg class="template-card-add" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <line x1="12" y1="5" x2="12" y2="19"/>
                    <line x1="5" y1="12" x2="19" y2="12"/>
                </svg>
                <div class="template-card-title">Upper Body Power (Push/Pull)</div>
            </div>
        </div>
    @else
        <!-- Show routines inside container -->
        @foreach($routines as $routine)
            <div class="routine-card" id="routine-{{ $routine->id }}" style="background: #f5f5f5; border: 1px solid #e0e0e0;">
                <div class="routine-card-header" onclick="toggleRoutine({{ $routine->id }})">
                    <div class="routine-card-info">
                        <div class="routine-icon" style="background: #fff;">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <rect x="3" y="4" width="18" height="18" rx="2" ry="2"/>
                                <line x1="16" y1="2" x2="16" y2="6"/>
                                <line x1="8" y1="2" x2="8" y2="6"/>
                                <line x1="3" y1="10" x2="21" y2="10"/>
                            </svg>
                        </div>
                        <div>
                            <div class="routine-name" style="color: #1a1a1a;">{{ $routine->name }}</div>
                            <div class="routine-meta">{{ $routine->exercises->count() }} exercises</div>
                        </div>
                    </div>
                    <svg class="routine-expand-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width: 20px; height: 20px; color: #666;">
                        <polyline points="6 9 12 15 18 9"/>
                    </svg>
                </div>
                <div class="routine-card-body">
                    @if($routine->exercises->isNotEmpty())
                        <div class="routine-exercises">
                            @foreach($routine->exercises as $idx => $ex)
                                <div class="routine-exercise-item" style="border-color: #e0e0e0; color: #1a1a1a;">
                                    <span>{{ $idx + 1 }}. {{ $ex->exercise->name }}</span>
                                    <span style="color: #666;">{{ $ex->target_sets ?? 3 }}×{{ $ex->target_reps ?? 8 }}</span>
                                </div>
                            @endforeach
                        </div>
                    @endif
                    <div class="routine-actions">
                        <a href="/routines/{{ $routine->id }}" class="routine-action-btn" style="background: #fff; color: #1a1a1a;">Edit</a>
                        <button class="routine-action-btn primary" onclick="startRoutine({{ $routine->id }})">Start</button>
                    </div>
                </div>
            </div>
        @endforeach
    @endif
</div>

<!-- User's Routines Section (if they have some outside the container) -->
@if($routines->isNotEmpty())
    <p class="section-title">MY ROUTINES</p>
    @foreach($routines as $routine)
        <div class="routine-card" id="routine-list-{{ $routine->id }}">
            <div class="routine-card-header" onclick="toggleRoutineList({{ $routine->id }})">
                <div class="routine-card-info">
                    <div class="routine-icon">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <rect x="3" y="4" width="18" height="18" rx="2" ry="2"/>
                            <line x1="16" y1="2" x2="16" y2="6"/>
                            <line x1="8" y1="2" x2="8" y2="6"/>
                            <line x1="3" y1="10" x2="21" y2="10"/>
                        </svg>
                    </div>
                    <div>
                        <div class="routine-name">{{ $routine->name }}</div>
                        <div class="routine-meta">{{ $routine->exercises->count() }} exercises</div>
                    </div>
                </div>
                <svg class="routine-expand-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width: 20px; height: 20px;">
                    <polyline points="6 9 12 15 18 9"/>
                </svg>
            </div>
            <div class="routine-card-body">
                @if($routine->exercises->isNotEmpty())
                    <div class="routine-exercises">
                        @foreach($routine->exercises as $idx => $ex)
                            <div class="routine-exercise-item">
                                <span>{{ $idx + 1 }}. {{ $ex->exercise->name }}</span>
                                <span style="color: var(--text-dim);">{{ $ex->target_sets ?? 3 }}×{{ $ex->target_reps ?? 8 }}</span>
                            </div>
                        @endforeach
                    </div>
                @endif
                <div class="routine-actions">
                    <a href="/routines/{{ $routine->id }}" class="routine-action-btn">Edit</a>
                    <button class="routine-action-btn primary" onclick="startRoutine({{ $routine->id }})">Start</button>
                    <button class="routine-action-btn" onclick="deleteRoutine({{ $routine->id }})" style="flex: 0.5;">🗑</button>
                </div>
            </div>
        </div>
    @endforeach
@endif

<!-- Add Routine Modal -->
<div id="addRoutineModal" class="modal-overlay">
    <div class="modal-content">
        <div class="modal-header">
            <h3 class="modal-title">Create New Routine</h3>
            <button class="modal-close" onclick="hideAddRoutineModal()">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <line x1="18" y1="6" x2="6" y2="18"/>
                    <line x1="6" y1="6" x2="18" y2="18"/>
                </svg>
            </button>
        </div>
        
        <form id="routineForm">
            <div class="form-group">
                <label class="form-label">Routine Name</label>
                <input type="text" name="name" class="form-input" placeholder="e.g., Push Day" required>
            </div>
            <div class="form-group">
                <label class="form-label">Description (optional)</label>
                <input type="text" name="description" class="form-input" placeholder="e.g., Chest, shoulders, triceps">
            </div>
            <button type="submit" class="btn btn-full">Create Routine</button>
            <button type="button" class="btn btn-secondary btn-full" style="margin-top: 8px;" onclick="hideAddRoutineModal()">Cancel</button>
        </form>
    </div>
</div>
@endsection

@section('scripts')
<script>
function toggleRoutine(id) {
    const card = document.getElementById('routine-' + id);
    card.classList.toggle('expanded');
}

function toggleRoutineList(id) {
    const card = document.getElementById('routine-list-' + id);
    card.classList.toggle('expanded');
}

function showAddRoutineModal() {
    document.getElementById('addRoutineModal').classList.add('active');
}

function hideAddRoutineModal() {
    document.getElementById('addRoutineModal').classList.remove('active');
}

async function createTemplateRoutine(name, description, exercises) {
    const res = await fetch('/routines', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
        },
        body: JSON.stringify({ name, description })
    });
    
    const data = await res.json();
    if (data.id) {
        for (const exerciseName of exercises) {
            const exerciseOptions = document.querySelectorAll('option');
            let exerciseId = null;
            for (const opt of exerciseOptions) {
                if (opt.textContent.trim() === exerciseName) {
                    exerciseId = opt.value;
                    break;
                }
            }
            
            if (exerciseId) {
                await fetch(`/routines/${data.id}/exercises`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    },
                    body: JSON.stringify({
                        exercise_id: exerciseId,
                        target_sets: 3,
                        target_reps: 8
                    })
                });
            }
        }
        
        window.location.href = `/routines/${data.id}`;
    }
}

document.getElementById('routineForm').addEventListener('submit', async function(e) {
    e.preventDefault();
    const formData = new FormData(e.target);
    const data = {
        name: formData.get('name'),
        description: formData.get('description')
    };
    
    const res = await fetch('/routines', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
        },
        body: JSON.stringify(data)
    });
    
    const result = await res.json();
    if (result.id) {
        window.location.href = `/routines/${result.id}`;
    }
});

async function deleteRoutine(id) {
    if (!confirm('Delete this routine?')) return;
    await fetch(`/routines/${id}`, {
        method: 'DELETE',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
        }
    });
    location.reload();
}

async function startRoutine(id) {
    const res = await fetch('/workouts', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
        },
        body: JSON.stringify({ routine_id: id })
    });
    const data = await res.json();
    window.location.href = '/workouts/create';
}
</script>
@endsection
