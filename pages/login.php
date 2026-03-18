<?php
// Clear any existing session errors
$error = $_GET['error'] ?? '';
$errorMessages = [
    'csrf' => 'Invalid security token. Please try again.',
    'invalid' => 'Invalid password.',
    'rate_limited' => 'Too many login attempts. Please try again later.',
    'unauthorized' => 'Please log in to continue.'
];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Workout Tracker</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Space+Mono:wght@400;700&display=swap" rel="stylesheet">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Space Mono', monospace;
            background: #0a0a0a;
            color: #e0e0e0;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }
        
        .login-container {
            width: 100%;
            max-width: 360px;
            background: #141414;
            border: 1px solid #2a2a2a;
            border-radius: 12px;
            padding: 40px 32px;
        }
        
        .logo {
            text-align: center;
            margin-bottom: 32px;
        }
        
        .logo svg {
            width: 80px;
            height: 48px;
        }
        
        h1 {
            font-size: 18px;
            text-transform: uppercase;
            letter-spacing: 2px;
            text-align: center;
            margin-bottom: 8px;
        }
        
        .subtitle {
            font-size: 12px;
            color: #666;
            text-align: center;
            margin-bottom: 32px;
        }
        
        .form-group {
            margin-bottom: 20px;
        }
        
        label {
            display: block;
            font-size: 11px;
            color: #888;
            text-transform: uppercase;
            letter-spacing: 1px;
            margin-bottom: 8px;
        }
        
        input[type="password"] {
            width: 100%;
            padding: 14px 16px;
            background: #0a0a0a;
            border: 1px solid #333;
            border-radius: 6px;
            color: #e0e0e0;
            font-family: inherit;
            font-size: 14px;
            outline: none;
            transition: border-color 0.2s;
        }
        
        input[type="password"]:focus {
            border-color: #ff6b35;
        }
        
        button {
            width: 100%;
            padding: 14px;
            background: #ff6b35;
            border: none;
            border-radius: 6px;
            color: #fff;
            font-family: inherit;
            font-size: 12px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 1px;
            cursor: pointer;
            transition: background 0.2s;
        }
        
        button:hover {
            background: #e55a2b;
        }
        
        .error {
            background: rgba(255, 0, 0, 0.1);
            border: 1px solid rgba(255, 0, 0, 0.3);
            border-radius: 6px;
            padding: 12px;
            margin-bottom: 20px;
            font-size: 12px;
            color: #ff6666;
        }
        
        footer {
            text-align: center;
            margin-top: 32px;
            font-size: 11px;
            color: #444;
        }
    </style>
</head>
<body>
    <div class="login-container">
        <div class="logo">
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
        
        <h1>Workout Tracker</h1>
        <p class="subtitle">Enter your password to continue</p>
        
        <?php if ($error && isset($errorMessages[$error])): ?>
            <div class="error"><?php echo h($errorMessages[$error]); ?></div>
        <?php endif; ?>
        
        <form method="POST" action="/api/login">
            <?php echo Security::csrfField(); ?>
            <div class="form-group">
                <label for="password">Password</label>
                <input type="password" id="password" name="password" required autofocus>
            </div>
            <button type="submit">Log In</button>
        </form>
        
        <footer>
            Secure Access Only
        </footer>
    </div>
</body>
</html>
