<?php
$user = getUser();
$db = getDB();

// Get stats
$stmt = $db->prepare("SELECT COUNT(*) as total_workouts FROM workouts WHERE user_id = ? AND ended_at IS NOT NULL");
$stmt->execute([$_SESSION['user_id']]);
$totalWorkouts = $stmt->fetch()['total_workouts'];

$stmt = $db->prepare("
    SELECT SUM(weight * reps) as total_volume 
    FROM workout_sets ws 
    JOIN workouts w ON ws.workout_id = w.id 
    WHERE w.user_id = ? AND w.ended_at IS NOT NULL
");
$stmt->execute([$_SESSION['user_id']]);
$totalVolume = $stmt->fetch()['total_volume'] ?? 0;

// Get recent workouts WITH volume per workout
$stmt = $db->prepare("
    SELECT w.*, COUNT(ws.id) as set_count, COALESCE(SUM(ws.weight * ws.reps), 0) as total_volume 
    FROM workouts w 
    LEFT JOIN workout_sets ws ON w.id = ws.workout_id
    WHERE w.user_id = ? AND w.ended_at IS NOT NULL 
    GROUP BY w.id 
    ORDER BY w.started_at DESC 
    LIMIT 5
");
$stmt->execute([$_SESSION['user_id']]);
$recentWorkouts = $stmt->fetchAll();

// 20K goal
$GOAL_VOLUME = 20000;

/**
 * Get exercises for a workout
 */
function getWorkoutExercises($db, $workoutId) {
    $stmt = $db->prepare("
        SELECT DISTINCT e.name 
        FROM workout_sets ws 
        JOIN exercises e ON ws.exercise_id = e.id 
        WHERE ws.workout_id = ?
    ");
    $stmt->execute([$workoutId]);
    return $stmt->fetchAll(PDO::FETCH_COLUMN);
}

/**
 * Get muscle groups from exercise names
 */
function getMuscleGroups($exercises) {
    $groups = [];
    foreach ($exercises as $name) {
        $nameLower = strtolower($name);
        if (str_contains($nameLower, 'press') || str_contains($nameLower, 'fly') || str_contains($nameLower, 'chest') || str_contains($nameLower, 'pec')) {
            $groups[] = 'chest';
        } elseif (str_contains($nameLower, 'row') || str_contains($nameLower, 'pull') || str_contains($nameLower, 'lat') || str_contains($nameLower, 'back') || str_contains($nameLower, 'extension')) {
            $groups[] = 'back';
        } elseif (str_contains($nameLower, 'squat') || str_contains($nameLower, 'leg') || str_contains($nameLower, 'calf')) {
            $groups[] = 'leg';
        } elseif (str_contains($nameLower, 'shoulder') || str_contains($nameLower, 'raise') || str_contains($nameLower, 'shrug')) {
            $groups[] = 'shoulders';
        } elseif (str_contains($nameLower, 'curl') || str_contains($nameLower, 'bicep') || str_contains($nameLower, 'tricep') || str_contains($nameLower, 'dip')) {
            $groups[] = 'arms';
        }
    }
    return array_unique($groups);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Workout Tracker</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Space+Mono:wght@400;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="/css/style.css">
    <style>
        .workout-item { 
            display: block; 
            background: var(--surface); 
            border: 1px solid var(--border); 
            border-radius: 8px; 
            padding: 16px; 
            margin-bottom: 12px; 
            text-decoration: none;
            color: inherit;
        }
        .workout-item-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 8px;
        }
        .workout-item-title { 
            font-size: 14px; 
            font-weight: 700; 
            color: var(--text);
            text-transform: uppercase;
        }
        .workout-item-date { 
            font-size: 11px; 
            color: var(--text-dim);
            margin-top: 4px;
        }
        .volume-badge {
            background: var(--accent);
            color: #000;
            padding: 4px 10px;
            border-radius: 4px;
            font-family: 'Space Mono', monospace;
            font-size: 13px;
            font-weight: 700;
        }
        .volume-badge.goal-met {
            background: var(--success, #4ade80);
        }
        .volume-bar-bg {
            height: 4px;
            background: var(--bg);
            border-radius: 2px;
            margin-top: 8px;
            overflow: hidden;
        }
        .volume-bar-fill {
            height: 100%;
            background: var(--accent);
            border-radius: 2px;
        }
        .volume-bar-fill.goal-met {
            background: var(--success, #4ade80);
        }
        .muscle-tags {
            display: flex;
            gap: 6px;
            flex-wrap: wrap;
            margin-top: 8px;
        }
        .muscle-tag {
            font-size: 9px;
            text-transform: uppercase;
            padding: 2px 8px;
            border-radius: 4px;
            font-weight: 700;
        }
        .muscle-tag.chest { background: rgba(255, 107, 53, 0.2); color: var(--accent); }
        .muscle-tag.back { background: rgba(74, 222, 128, 0.2); color: #4ade80; }
        .muscle-tag.leg { background: rgba(59, 130, 246, 0.2); color: #3b82f6; }
        .muscle-tag.shoulders { background: rgba(168, 85, 247, 0.2); color: #a855f7; }
        .muscle-tag.arms { background: rgba(234, 179, 8, 0.2); color: #eab308; }
        .goal-progress {
            background: var(--surface);
            border: 1px solid var(--border);
            border-radius: 8px;
            padding: 12px 16px;
            margin: 16px 0;
        }
        .goal-label {
            font-size: 10px;
            color: var(--text-dim);
            text-transform: uppercase;
            margin-bottom: 8px;
        }
        .goal-bar-bg {
            height: 8px;
            background: var(--bg);
            border-radius: 4px;
            overflow: hidden;
        }
        .goal-bar-fill {
            height: 100%;
            background: linear-gradient(90deg, var(--accent), var(--success, #4ade80));
            border-radius: 4px;
            transition: width 0.3s;
        }
        .goal-text {
            font-family: 'Space Mono', monospace;
            font-size: 12px;
            margin-top: 8px;
            text-align: center;
        }
    </style>
</head>
<body>
    <div class="app">
        <div class="content">
            <!-- Header with Logo -->
            <div class="page-header-center">
                <div class="header-logo">
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
            </div>
            
            <!-- Stats Overview -->
            <div class="stats-row">
                <div class="stat-box">
                    <div class="stat-box-label">WORKOUTS</div>
                    <div class="stat-box-value small"><?php echo $totalWorkouts; ?></div>
                </div>
                <div class="stat-box">
                    <div class="stat-box-label">TOTAL VOLUME</div>
                    <div class="stat-box-value small"><?php echo number_format($totalVolume / 1000, 1); ?>k</div>
                </div>
            </div>
            
            <?php 
            // Calculate goal progress
            $latestVolume = !empty($recentWorkouts) ? $recentWorkouts[0]['total_volume'] : 0;
            $goalPercent = min(($latestVolume / $GOAL_VOLUME) * 100, 100);
            $goalMet = $latestVolume >= $GOAL_VOLUME;
            ?>
            <div class="goal-progress">
                <div class="goal-label">Latest Workout vs 20K Goal</div>
                <div class="goal-bar-bg">
                    <div class="goal-bar-fill" style="width: <?php echo $goalPercent; ?>%"></div>
                </div>
                <div class="goal-text">
                    <?php echo number_format($latestVolume); ?> / 20,000 lbs 
                    <?php if ($goalMet): ?>
                        <span style="color: var(--success, #4ade80);">✓ GOAL MET!</span>
                    <?php else: ?>
                        (<?php echo number_format($GOAL_VOLUME - $latestVolume); ?> to go)
                    <?php endif; ?>
                </div>
            </div>
            
            <!-- Action Buttons -->
            <div class="action-buttons">
                <a href="/goals" class="action-btn-card">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <circle cx="12" cy="12" r="10"/>
                        <circle cx="12" cy="12" r="6"/>
                        <circle cx="12" cy="12" r="2"/>
                    </svg>
                    GOALS
                </a>
                <a href="/routines" class="action-btn-card">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <rect x="3" y="4" width="18" height="18" rx="2" ry="2"/>
                        <line x1="16" y1="2" x2="16" y2="6"/>
                        <line x1="8" y1="2" x2="8" y2="6"/>
                        <line x1="3" y1="10" x2="21" y2="10"/>
                    </svg>
                    ROUTINES
                </a>
            </div>
            
            <!-- Recent Workouts -->
            <p class="section-title">RECENT WORKOUTS</p>
            
            <?php if (empty($recentWorkouts)): ?>
                <div class="empty-state">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                        <path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"/>
                    </svg>
                    <p>No workouts yet. Start your first one!</p>
                </div>
            <?php else: ?>
                <div class="workout-list">
                    <?php foreach ($recentWorkouts as $workout): 
                        $exercises = getWorkoutExercises($db, $workout['id']);
                        $muscleGroups = getMuscleGroups($exercises);
                        $timestamp = $workout['started_at'] / 1000;
                        $workoutGoalMet = $workout['total_volume'] >= $GOAL_VOLUME;
                        $volumePercent = min(($workout['total_volume'] / $GOAL_VOLUME) * 100, 100);
                    ?>
                        <a href="/workouts/view?id=<?php echo $workout['id']; ?>" class="workout-item">
                            <div class="workout-item-header">
                                <div>
                                    <div class="workout-item-title">Workout #<?php echo $workout['id']; ?> — <?php echo date('l', $timestamp); ?></div>
                                    <div class="workout-item-date"><?php echo date('M j, Y', $timestamp); ?> • <?php echo $workout['set_count']; ?> sets</div>
                                </div>
                                <div class="volume-badge <?php echo $workoutGoalMet ? 'goal-met' : ''; ?>">
                                    <?php echo number_format($workout['total_volume']); ?> lbs
                                </div>
                            </div>
                            <div class="volume-bar-bg">
                                <div class="volume-bar-fill <?php echo $workoutGoalMet ? 'goal-met' : ''; ?>" style="width: <?php echo $volumePercent; ?>%"></div>
                            </div>
                            <?php if (!empty($muscleGroups)): ?>
                                <div class="muscle-tags">
                                    <?php foreach ($muscleGroups as $muscle): ?>
                                        <span class="muscle-tag <?php echo $muscle; ?>"><?php echo $muscle; ?></span>
                                    <?php endforeach; ?>
                                </div>
                            <?php endif; ?>
                        </a>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
        
        <!-- Bottom Navigation -->
        <nav>
            <a href="/dashboard" class="nav-btn active">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/>
                    <polyline points="9 22 9 12 15 12 15 22"/>
                </svg>
                Home
            </a>
            <a href="/workouts/create" class="nav-btn">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <circle cx="12" cy="12" r="10"/>
                    <line x1="12" y1="8" x2="12" y2="16"/>
                    <line x1="8" y1="12" x2="16" y2="12"/>
                </svg>
                Log
            </a>
            <a href="/workouts" class="nav-btn">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/>
                    <polyline points="14 2 14 8 20 8"/>
                    <line x1="16" y1="13" x2="8" y2="13"/>
                    <line x1="16" y1="17" x2="8" y2="17"/>
                    <polyline points="10 9 9 9 8 9"/>
                </svg>
                History
            </a>
            <a href="/stats" class="nav-btn">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <line x1="18" y1="20" x2="18" y2="10"/>
                    <line x1="12" y1="20" x2="12" y2="4"/>
                    <line x1="6" y1="20" x2="6" y2="14"/>
                </svg>
                Stats
            </a>
            <a href="/prs" class="nav-btn">
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
    </div>
</body>
</html>
