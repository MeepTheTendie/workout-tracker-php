@extends('layouts.app')

@section('title', 'Login')

@section('styles')
<style>
    .login-container {
        min-height: 100vh;
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        padding: 40px 20px;
    }
    
    .login-branding {
        text-align: center;
        margin-bottom: 32px;
    }
    
    /* Pixel Dumbbell Logo */
    .login-logo {
        width: 100px;
        height: 60px;
        margin: 0 auto 24px;
        image-rendering: pixelated;
    }
    
    .login-logo svg {
        width: 100%;
        height: 100%;
    }
    
    .login-title {
        font-size: 32px;
        font-weight: 700;
        letter-spacing: 4px;
        margin-bottom: 8px;
        color: var(--text);
    }
    
    /* White Card Form - matching mockup */
    .login-form-container {
        width: 100%;
        max-width: 400px;
        background: #fff;
        border-radius: 4px;
        padding: 32px;
    }
    
    .login-form-label {
        display: block;
        font-size: 12px;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 1px;
        color: #1a1a1a;
        margin-bottom: 8px;
    }
    
    .login-form-input {
        width: 100%;
        padding: 14px;
        background: #fff;
        border: 2px solid #e0e0e0;
        border-radius: 4px;
        font-family: 'Space Mono', monospace;
        font-size: 15px;
        color: #1a1a1a;
        transition: all 0.2s;
        margin-bottom: 20px;
    }
    
    .login-form-input:focus {
        outline: none;
        border-color: var(--accent);
    }
    
    .login-form-input::placeholder {
        color: #999;
    }
    
    .btn-login {
        width: 100%;
        padding: 16px;
        background: #1a1a1a;
        border: none;
        border-radius: 4px;
        color: #fff;
        font-family: 'Space Mono', monospace;
        font-size: 13px;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 1px;
        cursor: pointer;
        transition: all 0.2s;
        margin-top: 8px;
    }
    
    .btn-login:hover {
        background: #333;
    }
    
    .login-footer {
        margin-top: 20px;
        text-align: center;
        font-size: 12px;
        color: #666;
    }
    
    .login-footer a {
        color: var(--accent);
        text-decoration: none;
    }
    
    .login-footer a:hover {
        text-decoration: underline;
    }
    
    .login-footer .divider {
        margin: 0 8px;
        color: #ccc;
    }
    
    .welcome-text {
        text-align: center;
        margin-top: 40px;
        font-size: 14px;
        color: var(--text-dim);
        text-transform: uppercase;
        letter-spacing: 3px;
    }
    
    .error-message {
        color: #ff6b6b;
        font-size: 12px;
        margin-top: -12px;
        margin-bottom: 16px;
    }
</style>
@endsection

@section('content')
<div class="login-container">
    <!-- Pixel Dumbbell Logo -->
    <div class="login-branding">
        <div class="login-logo">
            <svg viewBox="0 0 100 60" fill="none" xmlns="http://www.w3.org/2000/svg">
                <!-- Bar -->
                <rect x="20" y="26" width="60" height="8" fill="#e0e0e0"/>
                <!-- Left weights - pixelated style -->
                <rect x="4" y="14" width="8" height="32" fill="#c0c0c0"/>
                <rect x="14" y="8" width="6" height="44" fill="#d0d0d0"/>
                <rect x="22" y="18" width="6" height="24" fill="#b0b0b0"/>
                <!-- Right weights -->
                <rect x="88" y="14" width="8" height="32" fill="#c0c0c0"/>
                <rect x="80" y="8" width="6" height="44" fill="#d0d0d0"/>
                <rect x="72" y="18" width="6" height="24" fill="#b0b0b0"/>
                <!-- Center grip -->
                <rect x="42" y="24" width="16" height="12" fill="#a0a0a0"/>
            </svg>
        </div>
        <h1 class="login-title">WORKOUT TRACKER</h1>
    </div>
    
    <!-- White Card Form -->
    <div class="login-form-container">
        <form method="POST" action="{{ route('login') }}">
            @csrf
            
            <label class="login-form-label">EMAIL</label>
            <input type="email" name="email" class="login-form-input" value="{{ old('email') }}" required autofocus>
            
            <label class="login-form-label">PASSWORD</label>
            <input type="password" name="password" class="login-form-input" required>
            
            @if ($errors->any())
                <div class="error-message">
                    {{ $errors->first() }}
                </div>
            @endif
            
            <button type="submit" class="btn-login">LOGIN</button>
        </form>
        
        <div class="login-footer">
            <a href="#">Forgot password?</a>
            <span class="divider">|</span>
            <a href="#">Sign up for a new account</a>
        </div>
    </div>
    
    <p class="welcome-text">WELCOME</p>
</div>
@endsection
