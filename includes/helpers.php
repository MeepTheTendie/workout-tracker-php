<?php
/**
 * Helper Functions
 * 
 * This file contains utility functions used throughout the application
 * for formatting, data processing, and UI rendering.
 * 
 * @package WorkoutTracker
 * @subpackage Core
 */

/**
 * Format timestamp for display
 * 
 * Handles both millisecond and second timestamps automatically.
 * Milliseconds are detected when timestamp > 1 trillion.
 * 
 * @param int|null $timestamp Unix timestamp (seconds or milliseconds)
 * @param string $format Date format string (default: 'M j, Y')
 * @return string Formatted date or 'N/A' if null
 */
function formatDate(?int $timestamp, string $format = 'M j, Y'): string
{
    if (!$timestamp) return 'N/A';
    // Handle both milliseconds and seconds
    if ($timestamp > 1000000000000) {
        $timestamp = (int)($timestamp / 1000);
    }
    return date($format, $timestamp);
}

/**
 * Format relative time (time ago)
 * 
 * Returns human-readable relative time like "just now", "5m ago", "2h ago"
 * 
 * @param int|null $timestamp Unix timestamp
 * @return string Relative time description or 'never' if null
 */
function timeAgo(?int $timestamp): string
{
    if (!$timestamp) return 'never';
    if ($timestamp > 1000000000000) {
        $timestamp = (int)($timestamp / 1000);
    }
    
    $diff = time() - $timestamp;
    
    if ($diff < 60) return 'just now';
    if ($diff < 3600) return floor($diff / 60) . 'm ago';
    if ($diff < 86400) return floor($diff / 3600) . 'h ago';
    if ($diff < 604800) return floor($diff / 86400) . 'd ago';
    
    return formatDate($timestamp);
}

/**
 * Format weight with units
 * 
 * @param float|null $weight Weight in pounds
 * @return string Formatted weight with 'lbs' suffix or '-' if null
 */
function formatWeight(?float $weight): string
{
    if ($weight === null) return '-';
    return number_format($weight, 1) . ' lbs';
}

/**
 * Format volume (weight × reps)
 * 
 * Calculates total volume for a set and formats with commas
 * 
 * @param float $weight Weight in pounds
 * @param int $reps Number of repetitions
 * @return string Formatted volume with 'lbs' suffix
 */
function formatVolume(float $weight, int $reps): string
{
    return number_format($weight * $reps) . ' lbs';
}

/**
 * Get current timestamp in milliseconds
 * 
 * @return int Current time as Unix timestamp in milliseconds
 */
function now(): int
{
    return (int)(microtime(true) * 1000);
}

/**
 * Get exercises grouped by category
 * 
 * Fetches all exercises from database and groups them by their category
 * for use in dropdown menus.
 * 
 * @return array Associative array with categories as keys, arrays of exercises as values
 */
function getExercisesByCategory(): array
{
    $exercises = dbFetchAll("SELECT * FROM exercises ORDER BY category, name");
    
    $grouped = [];
    foreach ($exercises as $ex) {
        $grouped[$ex['category']][] = $ex;
    }
    
    return $grouped;
}

/**
 * Get last used weight for each exercise
 * 
 * Queries the database to find the most recent weight used for each exercise
 * by the specified user. Used for progression suggestions.
 * 
 * @param int $userId User ID to query for
 * @return array Associative array with exercise names as keys, weights as values
 */
function getLastWeights(int $userId): array
{
    $sql = "
        SELECT e.name, ws.weight 
        FROM workout_sets ws 
        JOIN exercises e ON ws.exercise_id = e.id 
        JOIN workouts w ON ws.workout_id = w.id 
        WHERE w.user_id = ? 
          AND w.ended_at IS NOT NULL 
          AND ws.weight > 0
          AND ws.completed_at IS NOT NULL
        ORDER BY ws.completed_at DESC
    ";
    
    $rows = dbFetchAll($sql, [$userId]);
    
    $lastWeights = [];
    foreach ($rows as $row) {
        if (!isset($lastWeights[$row['name']])) {
            $lastWeights[$row['name']] = (float)$row['weight'];
        }
    }
    
    return $lastWeights;
}

/**
 * Calculate suggested next weight for progression
 * 
 * Uses predefined progression rules for specific exercises to suggest
 * the next weight to attempt. Includes rules for all exercises in
 * the 3-day functional fitness program.
 * 
 * @param string $exerciseName Name of the exercise
 * @param float|null $lastWeight Last weight used for this exercise
 * @return float|null Suggested next weight or null if no suggestion available
 */
function suggestNextWeight(string $exerciseName, ?float $lastWeight): ?float
{
    $rules = [
        // DAY A - Lower + Bench
        'Hack Squat' => ['increment' => 15, 'threshold' => 200, 'after_threshold' => 20],
        'Bench Press' => ['increment' => 10, 'threshold' => 150, 'after_threshold' => 15],
        'Romanian Deadlift' => ['increment' => 15, 'threshold' => 135, 'after_threshold' => 20],
        'Diverging Seated Row' => ['increment' => 10, 'threshold' => null, 'after_threshold' => null],
        'Leg Curl' => ['increment' => 10, 'threshold' => 100, 'after_threshold' => 15],
        'Crunch' => ['increment' => 5, 'threshold' => null, 'after_threshold' => null],
        // DAY B - Upper Pull + RDL
        'Barbell Row' => ['increment' => 10, 'threshold' => 115, 'after_threshold' => 15],
        'Lat Pulldown' => ['increment' => 10, 'threshold' => 130, 'after_threshold' => 15],
        'Face Pull' => ['increment' => 5, 'threshold' => null, 'after_threshold' => null],
        'Bicep Curl' => ['increment' => 5, 'threshold' => 50, 'after_threshold' => 10],
        'Low Back - Roc It' => ['increment' => 15, 'threshold' => 100, 'after_threshold' => 20],
        // DAY C - Tire + Conditioning
        'Tire Squats' => ['increment' => 20, 'threshold' => 400, 'after_threshold' => 30],
        'Chest Press' => ['increment' => 10, 'threshold' => 150, 'after_threshold' => 15],
        'Leg Extension' => ['increment' => 10, 'threshold' => 130, 'after_threshold' => 15],
        'Seated Dip' => ['increment' => 10, 'threshold' => 130, 'after_threshold' => 15],
        'Shrug' => ['increment' => 10, 'threshold' => 130, 'after_threshold' => 15],
        'Sled Push' => ['increment' => 10, 'threshold' => null, 'after_threshold' => null],
        // LEGACY - Backwards compatibility
        'Back Extension' => ['increment' => 15, 'threshold' => null, 'after_threshold' => null],
        'Leg Press' => ['increment' => 15, 'threshold' => null, 'after_threshold' => null],
        'Converging Chest Press' => ['increment' => 15, 'threshold' => null, 'after_threshold' => null],
        'Tricep Extensions' => ['increment' => 10, 'threshold' => null, 'after_threshold' => null],
        'Shoulder Press - Machine' => ['increment' => 20, 'threshold' => null, 'after_threshold' => null],
    ];
    
    if (!isset($rules[$exerciseName]) || $lastWeight === null) {
        return $lastWeight;
    }
    
    $rule = $rules[$exerciseName];
    $increment = $rule['increment'];
    
    // Apply threshold increase if applicable
    if ($rule['threshold'] !== null && $lastWeight >= $rule['threshold']) {
        $increment = $rule['after_threshold'] ?? $increment;
    }
    
    return $lastWeight + $increment;
}

/**
 * Get progression note for display
 * 
 * Returns a human-readable note about the progression rule for an exercise.
 * 
 * @param string $exerciseName Name of the exercise
 * @return string Progression note or empty string if no rule exists
 */
function progressionNote(string $exerciseName): string
{
    $notes = [
        // DAY A
        'Hack Squat' => '+15 lbs (then +20 after 200)',
        'Bench Press' => '+10 lbs (then +15 after 150)',
        'Romanian Deadlift' => '+15 lbs (then +20 after 135)',
        'Diverging Seated Row' => '+10 lbs',
        'Leg Curl' => '+10 lbs (then +15 after 100)',
        'Crunch' => '+5 lbs or +5 reps',
        // DAY B
        'Barbell Row' => '+10 lbs (then +15 after 115)',
        'Lat Pulldown' => '+10 lbs (then +15 after 130)',
        'Face Pull' => '+5 lbs or +5 reps',
        'Bicep Curl' => '+5 lbs (then +10 after 50)',
        'Low Back - Roc It' => '+15 lbs (then +20 after 100)',
        // DAY C
        'Tire Squats' => '+20 lbs (then +30 after 400)',
        'Chest Press' => '+10 lbs (then +15 after 150)',
        'Leg Extension' => '+10 lbs (then +15 after 130)',
        'Seated Dip' => '+10 lbs (then +15 after 130)',
        'Shrug' => '+10 lbs (then +15 after 130)',
        'Sled Push' => '+10 lbs or +2 reps',
        // LEGACY
        'Back Extension' => '+15 lbs',
        'Leg Press' => '+15 lbs',
        'Converging Chest Press' => '+15 lbs',
        'Tricep Extensions' => '+10 lbs',
        'Shoulder Press - Machine' => '+20 lbs',
    ];
    
    return $notes[$exerciseName] ?? '';
}

/**
 * Render page layout
 * 
 * Outputs the complete HTML page structure including head, navigation,
 * flash messages, and content area. Uses a mobile-first responsive design.
 * 
 * @param string $title Page title
 * @param callable $contentFn Function that outputs the page content
 * @param bool $showNav Whether to show bottom navigation (default: true)
 * @return void
 */
function renderPage(string $title, callable $contentFn, bool $showNav = true): void
{
    ?><!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= e($title) ?> - Workout Tracker</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Space+Mono:wght@400;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="/assets/css/style.css">
</head>
<body>
    <div class="app">
        <div class="content">
            <?php if ($flash = getFlash()): ?>
                <div class="flash flash-<?= e($flash['type']) ?>">
                    <?= e($flash['message']) ?>
                </div>
            <?php endif; ?>
            
            <?php $contentFn(); ?>
        </div>
        
        <?php if ($showNav): ?>
            <?php renderNav(); ?>
        <?php endif; ?>
    </div>
</body>
</html><?php
}

/**
 * Render bottom navigation
 * 
 * Outputs the bottom navigation bar with icons for Home, Log, History,
 * Routines, Stats, PRs, and Goals. Automatically highlights the active page.
 * 
 * @return void
 */
function renderNav(): void
{
    $path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
    ?>
    <nav>
        <!-- Home/Dashboard Link -->
        <a href="/dashboard" class="nav-btn<?= $path === '/dashboard' ? ' active' : '' ?>">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/>
                <polyline points="9 22 9 12 15 12 15 22"/>
            </svg>
            Home
        </a>
        
        <!-- Workout Log Link -->
        <a href="/workouts/log" class="nav-btn<?= $path === '/workouts/log' ? ' active' : '' ?>">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <circle cx="12" cy="12" r="10"/>
                <line x1="12" y1="8" x2="12" y2="16"/>
                <line x1="8" y1="12" x2="16" y2="12"/>
            </svg>
            Log
        </a>
        
        <!-- Workout History Link -->
        <a href="/workouts/history" class="nav-btn<?= $path === '/workouts/history' ? ' active' : '' ?>">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/>
                <polyline points="14 2 14 8 20 8"/>
            </svg>
            History
        </a>
        
        <!-- Routines Link -->
        <a href="/routines" class="nav-btn<?= $path === '/routines' ? ' active' : '' ?>">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M9 5H7a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2h10a2 2 0 0 0 2-2V7a2 2 0 0 0-2-2h-2"/>
                <rect x="9" y="3" width="6" height="4" rx="2"/>
            </svg>
            Routines
        </a>
        
        <!-- Stats Link -->
        <a href="/stats" class="nav-btn<?= $path === '/stats' ? ' active' : '' ?>">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <line x1="18" y1="20" x2="18" y2="10"/>
                <line x1="12" y1="20" x2="12" y2="4"/>
                <line x1="6" y1="20" x2="6" y2="14"/>
            </svg>
            Stats
        </a>
        
        <!-- PRs Link -->
        <a href="/prs" class="nav-btn<?= $path === '/prs' ? ' active' : '' ?>">
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
        
        <!-- Goals Link -->
        <a href="/goals" class="nav-btn<?= $path === '/goals' ? ' active' : '' ?>">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <circle cx="12" cy="12" r="10"/>
                <circle cx="12" cy="12" r="6"/>
                <circle cx="12" cy="12" r="2"/>
            </svg>
            Goals
        </a>
        
        <!-- Cardio Link -->
        <a href="/cardio" class="nav-btn<?= $path === '/cardio' ? ' active' : '' ?>">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <circle cx="12" cy="12" r="10"/>
                <path d="M12 6v6l4 2"/>
            </svg>
            Cardio
        </a>
        
        <!-- Export Link -->
        <a href="/export" class="nav-btn<?= $path === '/export' ? ' active' : '' ?>">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/>
                <polyline points="7 10 12 15 17 10"/>
                <line x1="12" y1="15" x2="12" y2="3"/>
            </svg>
            Export
        </a>
    </nav>
    <?php
}
