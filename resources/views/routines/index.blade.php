@extends('layouts.app')

@section('title', 'Routines')

@section('content')
@php
$routines = auth()->user()->routines()->with('exercises.exercise')->get();
$exercises = App\Models\Exercise::orderBy('name')->get();
@endphp

<div class="page-header">
    <div class="page-title">Routines</div>
    <button class="btn" onclick="toggleAddForm()" id="addBtn">+ New</button>
</div>

<div id="addForm" class="section" style="display: none; background: var(--bg);">
    <form id="routineForm">
        <div class="form-group">
            <label class="form-label">Routine Name</label>
            <input type="text" name="name" class="form-input" placeholder="e.g., Push Day" required>
        </div>
        <div class="form-group">
            <label class="form-label">Description (optional)</label>
            <input type="text" name="description" class="form-input" placeholder="e.g., Chest, shoulders, triceps">
        </div>
        <button type="submit" class="btn" style="width: 100%;">CREATE ROUTINE</button>
        <button type="button" class="btn btn-secondary" style="width: 100%; margin-top: 8px;" onclick="toggleAddForm()">CANCEL</button>
    </form>
</div>

<div class="section" style="padding: 0;">
    @if($routines->isEmpty())
        <div style="padding: 20px;">
            <div class="empty">No routines yet</div>
        </div>
    @else
        @foreach($routines as $routine)
            <a href="/routines/{{ $routine->id }}" style="text-decoration: none; color: inherit; display: block;">
                <div style="padding: 16px; border-bottom: 1px solid var(--border); display: flex; justify-content: space-between; align-items: center;">
                    <div>
                        <div style="font-weight: 700;">{{ strtoupper($routine->name) }}</div>
                        <div style="font-size: 12px; color: var(--text-dim);">{{ $routine->description ?: 'No description' }}</div>
                    </div>
                    <span style="color: var(--text-dim);">→</span>
                </div>
            </a>
        @endforeach
    @endif
</div>
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
</script>
@endsection
