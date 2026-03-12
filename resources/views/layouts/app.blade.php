<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Workout Tracker')</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Space+Mono:wght@400;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --bg: #fafafa;
            --surface: #fff;
            --border: #111;
            --text: #111;
            --text-dim: #666;
            --accent: #ff3e00;
        }
        
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        html, body {
            font-family: 'Space Mono', monospace;
            background: var(--border);
            color: var(--text);
            min-height: 100vh;
        }
        
        .app {
            max-width: 600px;
            margin: 0 auto;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }
        
        .content {
            flex: 1;
            padding: 24px;
            padding-bottom: 100px;
        }
        
        nav {
            position: fixed;
            bottom: 0;
            left: 0;
            right: 0;
            background: var(--surface);
            border-top: 2px solid var(--border);
            display: flex;
            padding: 8px;
            justify-content: center;
            gap: 8px;
        }
        
        .nav-btn {
            padding: 12px 16px;
            background: var(--surface);
            border: 2px solid var(--border);
            font-family: 'Space Mono', monospace;
            font-size: 12px;
            font-weight: 700;
            text-transform: uppercase;
            text-decoration: none;
            color: var(--text);
            cursor: pointer;
        }
        
        .nav-btn:hover, .nav-btn.active {
            background: var(--border);
            color: var(--surface);
        }
        
        .page-header {
            margin-bottom: 24px;
        }
        
        .page-title {
            font-size: 24px;
            font-weight: 700;
            text-transform: uppercase;
        }
        
        .section {
            background: var(--surface);
            border: 2px solid var(--border);
            padding: 16px;
            margin-bottom: 16px;
        }
        
        .btn {
            display: inline-block;
            padding: 12px 24px;
            background: var(--border);
            color: var(--surface);
            border: 2px solid var(--border);
            font-family: 'Space Mono', monospace;
            font-size: 14px;
            font-weight: 700;
            text-transform: uppercase;
            cursor: pointer;
            text-decoration: none;
            text-align: center;
        }
        
        .btn:hover {
            background: var(--surface);
            color: var(--border);
        }
        
        .btn-secondary {
            background: var(--surface);
            color: var(--border);
        }
        
        .btn-secondary:hover {
            background: var(--border);
            color: var(--surface);
        }
        
        .btn-full {
            width: 100%;
        }
        
        .form-group {
            margin-bottom: 16px;
        }
        
        .form-label {
            display: block;
            font-size: 12px;
            font-weight: 700;
            text-transform: uppercase;
            margin-bottom: 8px;
        }
        
        .form-input {
            width: 100%;
            padding: 12px;
            border: 2px solid var(--border);
            font-family: 'Space Mono', monospace;
            font-size: 16px;
            background: var(--surface);
        }
        
        .form-input:focus {
            outline: none;
            border-color: var(--accent);
        }
        
        .error {
            color: #ff4444;
            margin-top: 12px;
        }
        
        .empty {
            text-align: center;
            padding: 40px;
            color: var(--text-dim);
        }
        
        @yield('styles')
    </style>
</head>
<body>
    <div class="app">
        <div class="content">
            @yield('content')
        </div>
        
        @auth
        <nav>
            <a href="{{ route('dashboard') }}" class="nav-btn {{ request()->routeIs('dashboard') ? 'active' : '' }}">Home</a>
            <a href="{{ route('workouts.create') }}" class="nav-btn {{ request()->routeIs('workouts.create', 'workouts.edit') ? 'active' : '' }}">Log</a>
            <a href="{{ route('workouts.index') }}" class="nav-btn {{ request()->routeIs('workouts.index') ? 'active' : '' }}">History</a>
        </nav>
        @endauth
    </div>
    
    @yield('scripts')
</body>
</html>
