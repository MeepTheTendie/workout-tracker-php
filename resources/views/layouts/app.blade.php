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
            --bg: #1a1a1a;
            --surface: #242424;
            --surface-hover: #2d2d2d;
            --border: #333;
            --text: #e0e0e0;
            --text-dim: #888;
            --text-muted: #666;
            --accent: #ff6b35;
            --accent-hover: #ff8555;
            --success: #4ade80;
            --grid-line: rgba(255,255,255,0.03);
        }
        
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        html, body {
            font-family: 'Space Mono', monospace;
            background: var(--bg);
            color: var(--text);
            min-height: 100vh;
        }
        
        /* Dark textured grid background */
        body {
            background-image: 
                linear-gradient(var(--grid-line) 1px, transparent 1px),
                linear-gradient(90deg, var(--grid-line) 1px, transparent 1px);
            background-size: 20px 20px;
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
            padding: 20px;
            padding-bottom: 100px;
        }
        
        /* Pixelated Barbell Logo */
        .logo {
            display: inline-flex;
            align-items: center;
            gap: 12px;
        }
        
        .logo-icon {
            width: 40px;
            height: 24px;
            image-rendering: pixelated;
        }
        
        .logo-text {
            font-size: 18px;
            font-weight: 700;
            letter-spacing: 2px;
            color: var(--text);
        }
        
        /* Page Header with Branding */
        .page-header {
            margin-bottom: 24px;
            padding-bottom: 16px;
            border-bottom: 1px solid var(--border);
        }
        
        .page-header-top {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 8px;
        }
        
        .page-title {
            font-size: 20px;
            font-weight: 700;
            letter-spacing: 1px;
            color: var(--text);
        }
        
        .page-subtitle {
            font-size: 11px;
            color: var(--text-dim);
            text-transform: uppercase;
            letter-spacing: 2px;
        }
        
        /* Section Cards */
        .section {
            background: var(--surface);
            border: 1px solid var(--border);
            border-radius: 8px;
            padding: 16px;
            margin-bottom: 16px;
        }
        
        .section-header {
            display: flex;
            align-items: center;
            gap: 8px;
            margin-bottom: 16px;
            font-size: 12px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 1px;
            color: var(--text-dim);
        }
        
        .section-header-icon {
            width: 16px;
            height: 16px;
            opacity: 0.7;
        }
        
        /* Buttons */
        .btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            padding: 14px 24px;
            background: var(--accent);
            color: #fff;
            border: none;
            border-radius: 6px;
            font-family: 'Space Mono', monospace;
            font-size: 13px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 1px;
            cursor: pointer;
            text-decoration: none;
            transition: all 0.2s;
        }
        
        .btn:hover {
            background: var(--accent-hover);
            transform: translateY(-1px);
        }
        
        .btn-secondary {
            background: var(--surface);
            border: 1px solid var(--border);
            color: var(--text);
        }
        
        .btn-secondary:hover {
            background: var(--surface-hover);
            border-color: var(--text-dim);
        }
        
        .btn-full {
            width: 100%;
        }
        
        .btn-icon {
            width: 16px;
            height: 16px;
        }
        
        /* Icon Buttons */
        .icon-btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 40px;
            height: 40px;
            background: var(--surface);
            border: 1px solid var(--border);
            border-radius: 6px;
            color: var(--text);
            cursor: pointer;
            transition: all 0.2s;
        }
        
        .icon-btn:hover {
            background: var(--surface-hover);
            border-color: var(--accent);
            color: var(--accent);
        }
        
        /* Form Elements */
        .form-group {
            margin-bottom: 16px;
        }
        
        .form-label {
            display: block;
            font-size: 11px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 1px;
            color: var(--text-dim);
            margin-bottom: 8px;
        }
        
        .form-input {
            width: 100%;
            padding: 14px;
            background: var(--bg);
            border: 1px solid var(--border);
            border-radius: 6px;
            font-family: 'Space Mono', monospace;
            font-size: 15px;
            color: var(--text);
            transition: all 0.2s;
        }
        
        .form-input:focus {
            outline: none;
            border-color: var(--accent);
            box-shadow: 0 0 0 3px rgba(255, 107, 53, 0.1);
        }
        
        .form-input::placeholder {
            color: var(--text-muted);
        }
        
        /* Error Messages */
        .error {
            color: #ff6b6b;
            font-size: 13px;
            margin-top: 12px;
            padding: 12px;
            background: rgba(255, 107, 107, 0.1);
            border: 1px solid rgba(255, 107, 107, 0.3);
            border-radius: 6px;
        }
        
        /* Empty States */
        .empty {
            text-align: center;
            padding: 40px 20px;
            color: var(--text-dim);
        }
        
        .empty-icon {
            width: 48px;
            height: 48px;
            margin: 0 auto 16px;
            opacity: 0.3;
        }
        
        /* Suggestion Cards */
        .suggestion-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(140px, 1fr));
            gap: 12px;
        }
        
        .suggestion-card {
            background: rgba(255,255,255,0.03);
            border: 1px dashed var(--border);
            border-radius: 8px;
            padding: 16px;
            text-align: center;
            cursor: pointer;
            transition: all 0.2s;
        }
        
        .suggestion-card:hover {
            background: rgba(255,107,53,0.1);
            border-color: var(--accent);
            border-style: solid;
        }
        
        .suggestion-card .icon {
            width: 24px;
            height: 24px;
            margin: 0 auto 8px;
            color: var(--accent);
        }
        
        .suggestion-card .title {
            font-size: 11px;
            font-weight: 700;
            text-transform: uppercase;
            color: var(--text);
            margin-bottom: 4px;
        }
        
        .suggestion-card .desc {
            font-size: 10px;
            color: var(--text-dim);
        }
        
        /* Muscle Group Icons */
        .muscle-icon {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 20px;
            height: 20px;
            background: var(--bg);
            border-radius: 4px;
            font-size: 12px;
        }
        
        /* Navigation */
        nav {
            position: fixed;
            bottom: 0;
            left: 0;
            right: 0;
            background: var(--surface);
            border-top: 1px solid var(--border);
            display: flex;
            padding: 8px;
            justify-content: center;
            gap: 4px;
            z-index: 100;
        }
        
        .nav-btn {
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 4px;
            padding: 8px 12px;
            background: transparent;
            border: none;
            font-family: 'Space Mono', monospace;
            font-size: 9px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            color: var(--text-dim);
            text-decoration: none;
            cursor: pointer;
            border-radius: 6px;
            transition: all 0.2s;
        }
        
        .nav-btn svg {
            width: 20px;
            height: 20px;
            opacity: 0.6;
        }
        
        .nav-btn:hover, .nav-btn.active {
            color: var(--accent);
            background: rgba(255,107,53,0.1);
        }
        
        .nav-btn:hover svg, .nav-btn.active svg {
            opacity: 1;
            color: var(--accent);
        }
        
        /* Data Cards */
        .data-card {
            background: var(--surface);
            border: 1px solid var(--border);
            border-radius: 8px;
            padding: 16px;
            margin-bottom: 12px;
        }
        
        .data-card-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 12px;
        }
        
        .data-card-title {
            font-size: 13px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        
        .data-card-meta {
            font-size: 11px;
            color: var(--text-dim);
        }
        
        /* Progress Bars */
        .progress-bar {
            height: 6px;
            background: var(--bg);
            border-radius: 3px;
            overflow: hidden;
        }
        
        .progress-bar-fill {
            height: 100%;
            background: var(--accent);
            border-radius: 3px;
            transition: width 0.3s ease;
        }
        
        /* Filters */
        .filter-bar {
            display: flex;
            gap: 8px;
            margin-bottom: 16px;
            overflow-x: auto;
            padding-bottom: 4px;
        }
        
        .filter-btn {
            padding: 8px 14px;
            background: var(--surface);
            border: 1px solid var(--border);
            border-radius: 20px;
            font-family: 'Space Mono', monospace;
            font-size: 11px;
            color: var(--text-dim);
            cursor: pointer;
            white-space: nowrap;
            transition: all 0.2s;
        }
        
        .filter-btn:hover, .filter-btn.active {
            background: var(--accent);
            border-color: var(--accent);
            color: #fff;
        }
        
        /* Trend Chart */
        .trend-chart {
            display: flex;
            align-items: flex-end;
            gap: 4px;
            height: 60px;
            padding: 8px 0;
        }
        
        .trend-bar {
            flex: 1;
            background: var(--accent);
            border-radius: 2px 2px 0 0;
            opacity: 0.7;
            min-height: 4px;
        }
        
        .trend-bar:hover {
            opacity: 1;
        }
        
        /* Responsive */
        @media (max-width: 480px) {
            .content {
                padding: 16px;
            }
            
            .nav-btn {
                padding: 6px 8px;
                font-size: 8px;
            }
            
            .nav-btn svg {
                width: 18px;
                height: 18px;
            }
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
            <a href="{{ route('dashboard') }}" class="nav-btn {{ request()->routeIs('dashboard') ? 'active' : '' }}">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/>
                    <polyline points="9 22 9 12 15 12 15 22"/>
                </svg>
                Home
            </a>
            <a href="{{ route('workouts.create') }}" class="nav-btn {{ request()->routeIs('workouts.create', 'workouts.edit') ? 'active' : '' }}">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <circle cx="12" cy="12" r="10"/>
                    <line x1="12" y1="8" x2="12" y2="16"/>
                    <line x1="8" y1="12" x2="16" y2="12"/>
                </svg>
                Log
            </a>
            <a href="{{ route('workouts.index') }}" class="nav-btn {{ request()->routeIs('workouts.index', 'workouts.show') ? 'active' : '' }}">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/>
                    <polyline points="14 2 14 8 20 8"/>
                    <line x1="16" y1="13" x2="8" y2="13"/>
                    <line x1="16" y1="17" x2="8" y2="17"/>
                    <polyline points="10 9 9 9 8 9"/>
                </svg>
                History
            </a>
            <a href="{{ route('stats.index') }}" class="nav-btn {{ request()->routeIs('stats.index') ? 'active' : '' }}">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <line x1="18" y1="20" x2="18" y2="10"/>
                    <line x1="12" y1="20" x2="12" y2="4"/>
                    <line x1="6" y1="20" x2="6" y2="14"/>
                </svg>
                Stats
            </a>
            <a href="{{ route('prs.index') }}" class="nav-btn {{ request()->routeIs('prs.index') ? 'active' : '' }}">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M6 9H4.5a2.5 2.5 0 0 1 0-5H6"/>
                    <path d="M18 9h1.5a2.5 2.5 0 0 0 0-5H18"/>
                    <path d="M4 22h16"/>
                    <path d="M10 14.66V17c0 .55-.47.98-.97 1.21C7.85 18.75 7 20.24 7 22"/>
                    <path d="M14 14.66V17c0 .55.47.98.97 1.21C16.15 18.75 17 20.24 17 22"/>
                    <path d="M18 2H6v7a6 6 0 0 0 12 0V2Z"/>
                </svg>
                PRs
            </a>
        </nav>
        @endauth
    </div>
    
    @yield('scripts')
</body>
</html>
