<?php
/**
 * Create Routine Page - Redesigned
 */

renderPage('Create Routine', function() {
    ?>
    <h1>CREATE ROUTINE</h1>
    
    <form method="POST" action="/action/routines/create">
        <?= csrfField() ?>
        
        <div class="form-group">
            <label class="form-label">ROUTINE NAME</label>
            <input type="text" name="name" class="form-input" placeholder="e.g., Upper Body Day" required>
        </div>
        
        <div class="form-group">
            <label class="form-label">DESCRIPTION (OPTIONAL)</label>
            <input type="text" name="description" class="form-input" placeholder="e.g., Chest, shoulders, triceps">
        </div>
        
        <button type="submit" class="btn btn-primary">CREATE ROUTINE</button>
        <a href="/routines" class="btn btn-secondary" style="margin-top: 12px;">CANCEL</a>
    </form>
    <?php
});
