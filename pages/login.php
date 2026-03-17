<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Workout Tracker</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Space+Mono:wght@400;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="/css/style.css">
</head>
<body>
    <div class="login-container">
        <div class="login-box">
            <div class="login-logo">
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
                <h1>WORKOUT TRACKER</h1>
            </div>
            
            <?php if (isset($_GET['error'])): ?>
                <div class="error">Invalid password</div>
            <?php endif; ?>
            
            <form class="login-form" method="POST" action="/api/login">
                <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
                <div class="form-group">
                    <label class="form-label">Password</label>
                    <input type="password" name="password" class="form-input" placeholder="Enter password" required autofocus>
                </div>
                <button type="submit" class="btn">LOGIN</button>
            </form>
        </div>
    </div>
</body>
</html>
