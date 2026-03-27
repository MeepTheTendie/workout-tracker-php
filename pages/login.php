<?php
/**
 * Login Page
 */

renderPage('Login', function() {
    ?>
    <div style="max-width: 320px; margin: 60px auto; text-align: center;">
        <h1 style="margin-bottom: 32px;">Workout Tracker</h1>
        
        <form method="POST" action="/action/auth/login">
            <?= csrfField() ?>
            
            <div class="form-group">
                <label class="form-label">Password</label>
                <input 
                    type="password" 
                    name="password" 
                    class="form-input" 
                    placeholder="Enter password"
                    required
                    autofocus
                >
            </div>
            
            <button type="submit" class="btn btn-primary">
                LOGIN
            </button>
        </form>
    </div>
    <?php
}, false);
