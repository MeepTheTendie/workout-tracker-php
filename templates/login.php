<div class="page-header">
    <div class="page-title">LOGIN</div>
</div>

<div class="section">
    <form id="loginForm" class="form">
        <div class="form-group">
            <label class="form-label">Password</label>
            <input type="password" name="password" class="form-input" required autofocus>
        </div>
        <button type="submit" class="btn btn-full">LOGIN</button>
        <?php if (!empty($_GET['error'])): ?>
            <div class="error" style="color: #ff4444; margin-top: 12px;">Incorrect password</div>
        <?php endif; ?>
    </form>
</div>

<script>
document.getElementById('loginForm')?.addEventListener('submit', async (e) => {
    e.preventDefault();
    const password = e.target.password.value;
    
    const res = await fetch('/api/auth.php?action=login', {
        method: 'POST',
        headers: {'Content-Type': 'application/json'},
        body: JSON.stringify({password})
    });
    
    if (res.ok) {
        location.href = '/';
    } else {
        location.href = '/?page=login&error=1';
    }
});
</script>
