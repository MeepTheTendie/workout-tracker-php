@extends('layouts.app')

@section('title', 'My Routines')

@section('styles')
<style>
    .page-header-routines {
        display: flex;
        align-items: center;
        justify-content: space-between;
        margin-bottom: 20px;
        padding-bottom: 16px;
        border-bottom: 1px solid var(--border);
    }
    
    .page-header-routines-left {
        display: flex;
        align-items: center;
        gap: 12px;
    }
    
    .page-header-routines-left svg {
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
    
    .new-routine-btn {
        display: flex;
        align-items: center;
        gap: 6px;
        padding: 10px 16px;
        background: var(--accent);
        border: none;
        border-radius: 6px;
        color: #fff;
        font-family: 'Space Mono', monospace;
        font-size: 11px;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        cursor: pointer;
        transition: all 0.2s;
    }
    
    .new-routine-btn:hover {
        background: var(--accent-hover);
    }
    
    .new-routine-btn svg {
        width: 14px;
        height: 14px;
    }
    
    /* Suggestion Templates */
    .routine-suggestions {
        margin-bottom: 20px;
    }
    
    .suggestions-title {
        font-size: 11px;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 1px;
        color: var(--text-dim);
        margin-bottom: 12px;
    }
    
    .suggestion-grid {
        display: grid;
        grid-template-columns: repeat(2, 1fr);
        gap: 12px;
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
    
    /* Routine Cards */
    .routine-card {
        background: var(--surface);
        border: 1px solid var(--border);
        border-radius: 10px;
        overflow: hidden;
        margin-bottom: 12px;
        transition: all 0.2s;
    }
    
    .routine-card:hover {
        border-color: var(--accent);
    }
    
    .routine-card-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 16px;
        cursor: pointer;
        transition: background 0.2s;
    }
    
    .routine-card-header:hover {
        background: var(--surface-hover);
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
        letter-spacing: 0.5px;
        margin-bottom: 2px;
    }
    
    .routine-meta {
        font-size: 11px;
        color: var(--text-dim);
    }
    
    .routine-expand-icon {
        width: 20px;
        height: 20px;
        color: var(--text-dim);
        transition: transform 0.2s;
    }
    
    .routine-card.expanded .routine-expand-icon {
        transform: rotate(180deg);
    }
    
    .routine-card-body {
        display: none;
        padding: 0 16px 16px;
        border-top: 1px solid var(--border);
    }
    
    .routine-card.expanded .routine-card-body {
        display: block;
    }
    
    .routine-description {
        font-size: 12px;
        color: var(--text-dim);
        padding: 12px 0;
    }
    
    .routine-exercises {
        margin-bottom: 12px;
    }
    
    .routine-exercise-item {
        display: flex;
        align-items: center;
        gap: 10px;
        padding: 10px 0;
        border-bottom: 1px solid var(--border);
        font-size: 12px;
    }
    
    .routine-exercise-item:last-child {
        border-bottom: none;
    }
    
    .routine-exercise-number {
        width: 20px;
        height: 20px;
        background: var(--bg);
        border-radius: 4px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 10px;
        color: var(--text-dim);
    }
    
    .routine-exercise-name {
        flex: 1;
        font-weight: 600;
    }
    
    .routine-exercise-targets {
        font-size: 10px;
        color: var(--text-dim);
    }
    
    .routine-actions {
        display: flex;
        gap: 8px;
    }
    
    .routine-action-btn {
        flex: 1;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 6px;
        padding: 12px;
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
        transition: all 0.2s;
    }
    
    .routine-action-btn:hover {
        background: var(--surface-hover);
        border-color: var(--accent);
        color: var(--accent);
    }
    
    .routine-action-btn.primary {
        background: var(--accent);
        border-color: var(--accent);
        color: #fff;
    }
    
    .routine-action-btn.primary:hover {
        background: var(--accent-hover);
    }
    
    .routine-action-btn svg {
        width: 14px;
        height: 14px;
    }
    
    .routine-action-btn.delete:hover {
        border-color: #ff6b6b;
        color: #ff6b6b;
    }
    
    /* Empty State */
    .empty-state {
        text-align: center;
        padding: 60px 20px;
        color: var(--text-dim);
    }
    
    .empty-state svg {
        width: 56px;
        height: 56px;
        margin: 0 auto 20px;
        opacity: 0.3;
    }
    
    .empty-state h3 {
        font-size: 14px;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 1px;
        margin-bottom: 8px;
        color: var(--text);
    }
    
    .empty-state p {
        font-size: 12px;
        margin-bottom: 20px;
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
        border-radius: 12px;
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
    
    /* Muscle Group Panels */
    .muscle-panels {
        margin-bottom: 20px;
    }
    
    .muscle-panel {
        background: var(--surface);
        border: 1px solid var(--border);
        border-radius: 10px;
        margin-bottom: 8px;
        overflow: hidden;
    }
    
    .muscle-panel-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 14px 16px;
        cursor: pointer;
        transition: background 0.2s;
    }
    
    .muscle-panel-header:hover {
        background: var(--surface-hover);
    }
    
    .muscle-panel-title {
        display: flex;
        align-items: center;
        gap: 10px;
        font-size: 13px;
        font-weight: 700;
        text-transform: uppercase;
    }
    
    .muscle-panel-title svg {
        width: 18px;
        height: 18px;
        color: var(--accent);
    }
    
    .muscle-panel-count {
        font-size: 11px;
        color: var(--text-dim);
        background: var(--bg);
        padding: 2px 8px;
        border-radius: 10px;
    }
    
    .muscle-panel-body {
        display: none;
        padding: 0 16px 16px;
        border-top: 1px solid var(--border);
    }
    
    .muscle-panel.expanded .muscle-panel-body {
        display: block;
    }
    
    .muscle-panel.expanded .routine-expand-icon {
        transform: rotate(180deg);
    }
</style>
@endsection

@section('content')
@php
$routines = auth()->user()->routines()->with('exercises.exercise')->get();
$exercises = App\Models\Exercise::orderBy('name')->get();

// Group routines by muscle focus
$chestRoutines = [];
$backRoutines = [];
$legRoutines = [];
$pushRoutines = [];
$pullRoutines = [];
$fullBodyRoutines = [];

foreach ($routines as $routine) {
    $name = strtolower($routine->name);
    if (str_contains($name, 'chest') || str_contains($name, 'push')) {
        $pushRoutines[] = $routine;
    } elseif (str_contains($name, 'back') || str_contains($name, 'pull')) {
        $pullRoutines[] = $routine;
    } elseif (str_contains($name, 'leg') || str_contains($name, 'squat')) {
        $legRoutines[] = $routine;
    } elseif (str_contains($name, 'full') || str_contains($name, 'total')) {
        $fullBodyRoutines[] = $routine;
    } else {
        $fullBodyRoutines[] = $routine;
    }
}
@endphp

<!-- Header with Branding -->
<div class="page-header-routines">
    <div class="page-header-routines-left">
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
            <h1>MY ROUTINES</h1>
            <p>Build once. Use forever.</p>
        </div>
    </div>
    <button class="new-routine-btn" onclick="showAddRoutineModal()">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <line x1="12" y1="5" x2="12" y2="19"/>
            <line x1="5" y1="12" x2="19" y2="12"/>
        </svg>
        New
    </button>
</div>

@if($routines->isEmpty())
    <!-- Suggestion Templates for Empty State -->
    <div class="routine-suggestions">
        <p class="suggestions-title">Quick Start Templates</p>
        <div class="suggestion-grid">
            <div class="suggestion-card" onclick="createTemplateRoutine('Push Day', 'Chest, Shoulders, Triceps', ['Bench Press', 'Shoulder Press - Machine', 'Tricep Pushdown'])">
                <svg class="icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M6 9H4.5a2.5 2.5 0 0 1 0-5H6"/>
                    <path d="M18 9h1.5a2.5 2.5 0 0 0 0-5H18"/>
                    <path d="M4 22h16"/>
                    <path d="M10 14.66V17c0 .55-.47.98-.97 1.21C7.85 18.75 7 20.24 7 22"/>
                    <path d="M14 14.66V17c0 .55.47.98.97 1.21C16.15 18.75 17 20.24 17 22"/>
                    <path d="M18 2H6v7a6 6 0 0 0 12 0V2Z"/>
                </svg>
                <div class="title">Push Day</div>
                <div class="desc">Chest, shoulders<br>triceps</div>
            </div>
            <div class="suggestion-card" onclick="createTemplateRoutine('Pull Day', 'Back, Biceps, Rear Delts', ['Lat Pulldown', 'Barbell Row', 'Bicep Curl'])">
                <svg class="icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M12 2L2 7l10 5 10-5-10-5zM2 17l10 5 10-5M2 12l10 5 10-5"/>
                </svg>
                <div class="title">Pull Day</div>
                <div class="desc">Back, biceps<br>rear delts</div>
            </div>
            <div class="suggestion-card" onclick="createTemplateRoutine('Leg Day', 'Quads, Hamstrings, Calves', ['Squat', 'Leg Press', 'Leg Curl', 'Calf Raise'])">
                <svg class="icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M12 3v18M8 21l4-4 4 4M8 3l4 4 4-4"/>
                </svg>
                <div class="title">Leg Day</div>
                <div class="desc">Quads, hams<br>calves</div>
            </div>
            <div class="suggestion-card" onclick="createTemplateRoutine('Full Body', 'Compound movements everywhere', ['Squat', 'Bench Press', 'Barbell Row', 'Overhead Press'])">
                <svg class="icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <circle cx="12" cy="12" r="10"/>
                    <circle cx="12" cy="12" r="3"/>
                </svg>
                <div class="title">Full Body</div>
                <div class="desc">3-day split<br>compound focus</div>
            </div>
        </div>
    </div>
    
    <div class="empty-state">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
            <rect x="3" y="4" width="18" height="18" rx="2" ry="2"/>
            <line x1="16" y1="2" x2="16" y2="6"/>
            <line x1="8" y1="2" x2="8" y2="6"/>
            <line x1="3" y1="10" x2="21" y2="10"/>
        </svg>
        <h3>No Routines Yet</h3>
        <p>Create your first routine or use a template above.</p>
        <button class="btn" onclick="showAddRoutineModal()">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="vertical-align: middle; margin-right: 6px;">
                <line x1="12" y1="5" x2="12" y2="19"/>
                <line x1="5" y1="12" x2="19" y2="12"/>
            </svg>
            Create Routine
        </button>
    </div>
@else
    <!-- Routines List -->
    @foreach($routines as $routine)
        <div class="routine-card" id="routine-{{ $routine->id }}">
            <div class="routine-card-header" onclick="toggleRoutine({{ $routine->id }})">
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
                <svg class="routine-expand-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <polyline points="6 9 12 15 18 9"/>
                </svg>
            </div>
            <div class="routine-card-body">
                @if($routine->description)
                    <p class="routine-description">{{ $routine->description }}</p>
                @endif
                
                @if($routine->exercises->isNotEmpty())
                    <div class="routine-exercises">
                        @foreach($routine->exercises as $idx => $ex)
                            <div class="routine-exercise-item">
                                <span class="routine-exercise-number">{{ $idx + 1 }}</span>
                                <span class="routine-exercise-name">{{ $ex->exercise->name }}</span>
                                <span class="routine-exercise-targets">
                                    {{ $ex->target_sets ?? 3 }}×{{ $ex->target_reps ?? 8 }}
                                    @if($ex->target_weight)
                                        @ {{ $ex->target_weight }} lbs
                                    @endif
                                </span>
                            </div>
                        @endforeach
                    </div>
                @endif
                
                <div class="routine-actions">
                    <a href="/routines/{{ $routine->id }}" class="routine-action-btn">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/>
                            <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/>
                        </svg>
                        Edit
                    </a>
                    <button class="routine-action-btn primary" onclick="startRoutine({{ $routine->id }})">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <polygon points="5 3 19 12 5 21 5 3"/>
                        </svg>
                        Start
                    </button>
                    <button class="routine-action-btn delete" onclick="deleteRoutine({{ $routine->id }})">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <polyline points="3 6 5 6 21 6"/>
                            <path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/>
                        </svg>
                    </button>
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

function showAddRoutineModal() {
    document.getElementById('addRoutineModal').classList.add('active');
}

function hideAddRoutineModal() {
    document.getElementById('addRoutineModal').classList.remove('active');
}

async function createTemplateRoutine(name, description, exercises) {
    // Create the routine first
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
        // Add exercises to the routine
        for (const exerciseName of exercises) {
            // Find exercise ID by name
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
