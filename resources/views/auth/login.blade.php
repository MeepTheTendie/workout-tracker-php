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
        margin-bottom: 40px;
    }
    
    .login-logo {
        width: 80px;
        height: 48px;
        margin: 0 auto 20px;
        image-rendering: pixelated;
    }
    
    .login-logo svg {
        width: 100%;
        height: 100%;
        fill: var(--accent);
    }
    
    .login-title {
        font-size: 28px;
        font-weight: 700;
        letter-spacing: 4px;
        margin-bottom: 8px;
        color: var(--text);
    }
    
    .login-subtitle {
        font-size: 12px;
        color: var(--text-dim);
        letter-spacing: 2px;
        text-transform: uppercase;
    }
    
    .login-form-container {
        width: 100%;
        max-width: 360px;
        background: var(--surface);
        border: 1px solid var(--border);
        border-radius: 12px;
        padding: 32px;
    }
    
    .login-welcome {
        text-align: center;
        margin-bottom: 24px;
        font-size: 13px;
        color: var(--text-dim);
        text-transform: uppercase;
        letter-spacing: 2px;
    }
    
    .btn-login {
        margin-top: 8px;
    }
    
    .login-footer {
        margin-top: 24px;
        text-align: center;
        font-size: 11px;
        color: var(--text-muted);
    }
</style>
@endsection

@section('content')
<div class="login-container">
    <div class="login-branding">
        <!-- Pixelated Barbell Logo -->
        <div class="login-logo">
            <svg viewBox="0 0 80 48" fill="none" xmlns="http://www.w3.org/2000/svg">
                <!-- Bar -->
                <rect x="12" y="22" width="56" height="4" fill="currentColor"/>
                <!-- Left weights -->
                <rect x="4" y="14" width="4" height="20" fill="currentColor"/>
                <rect x="10" y="10" width="4" height="28" fill="currentColor"/>
                <rect x="16" y="16" width="4" height="16" fill="currentColor"/>
                <!-- Right weights -->
                <rect x="72" y="14" width="4" height="20" fill="currentColor"/>
                <rect x="66" y="10" width="4" height="28" fill="currentColor"/>
                <rect x="60" y="16" width="4" height="16" fill="currentColor"/>
                <!-- Center grip detail -->
                <rect x="36" y="20" width="8" height="8" fill="currentColor" opacity="0.5"/>
            </svg>
        </div>
        <h1 class="login-title">WORKOUT<br>TRACKER</h1>
        <p class="login-subtitle">Track. Progress. Repeat.</p>
    </div>
    
    <div class="login-form-container">
        <p class="login-welcome">Welcome Back</p>
        
        <form method="POST" action="{{ route('login') }}">
            @csrf
            
            <div class="form-group">
                <label class="form-label">Email</label>
                <input type="email" name="email" class="form-input" value="{{ old('email') }}" required autofocus placeholder="your@email.com">
            </div>
            
            <div class="form-group">
                <label class="form-label">Password</label>
                <input type="password" name="password" class="form-input" required placeholder="••••••••">
            </div>
            
            <button type="submit" class="btn btn-full btn-login">
                <svg class="btn-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M15 3h4a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2h-4"/>
                    <polyline points="10,17 15,12 10,7"/>
                    <line x1="15" y1="12" x2="3" y2="12"/>
                </svg>
                Sign In
            </button>
            
            @if ($errors->any())
                <div class="error">
                    {{ $errors->first() }}
                </div>
            @endif
        </form>
    </div>
    
    <p class="login-footer">Built for lifters, by lifters.</p>
</div>
@endsection
