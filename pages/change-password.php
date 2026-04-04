<?php
/**
 * Change Password Page
 */

requireAuth();

$user = currentUser();

renderPage('Change Password', function() use ($user) {
    ?>
    <h1>Change Password</h1>
    
    <div class="card" style="max-width: 400px;">
        <form method="POST" action="/action/auth/change-password">
            <?= csrfField() ?>
            
            <div class="form-group">
                <label class="form-label">Current Password</label>
                <input 
                    type="password" 
                    name="current_password" 
                    class="form-input" 
                    placeholder="Enter current password"
                    required
                    autofocus
                >
            </div>
            
            <div class="form-group">
                <label class="form-label">New Password</label>
                <input 
                    type="password" 
                    name="new_password" 
                    class="form-input" 
                    placeholder="Enter new password (min 8 chars)"
                    required
                    minlength="8"
                >
            </div>
            
            <div class="form-group">
                <label class="form-label">Confirm New Password</label>
                <input 
                    type="password" 
                    name="confirm_password" 
                    class="form-input" 
                    placeholder="Confirm new password"
                    required
                    minlength="8"
                >
            </div>
            
            <div style="display: flex; gap: 12px; margin-top: 24px;">
                <a href="/dashboard" class="btn" style="flex: 1;">Cancel</a>
                <button type="submit" class="btn btn-primary" style="flex: 1;">
                    Change Password
                </button>
            </div>
        </form>
    </div>
    <?php
});
