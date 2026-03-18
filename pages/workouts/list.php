<?php
requireAuth();
$db = getDB();

// Get all workouts with volume
$stmt = $db->prepare("
    SELECT w.*, COUNT(ws.id) as set_count, COALESCE(SUM(ws.weight * ws.reps), 0) as total_volume 
    FROM workouts w 
    LEFT JOIN workout_sets ws ON w.id = ws.workout_id
    WHERE w.user_id = ? AND w.ended_at IS NOT NULL 
    GROUP BY w.id 
    ORDER BY w.started_at DESC
");
$stmt->execute([$_SESSION['user_id']]);
$workouts = $stmt->fetchAll();

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

// Calculate stats
$totalWorkouts = count($workouts);
$avgVolume = $totalWorkouts > 0 ? array_sum(array_column($workouts, 'total_volume')) / $totalWorkouts : 0;
$maxVolume = $totalWorkouts > 0 ? max(array_column($workouts, 'total_volume')) : 0;
$workoutsAtGoal = array_filter($workouts, fn($w) => $w['total_volume'] >= 20000);

// 20K goal
$GOAL_VOLUME = 20000;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Workout History - Workout Tracker</title>
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
            transition: all 0.2s;
        }
        .workout-item:hover {
            border-color: var(--accent);
            transform: translateY(-2px);
        }
        .workout-item-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 12px;
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
            white-space: nowrap;
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
            border-radius: 2px;
            transition: width 0.3s;
        }
        .volume-bar-fill.goal-met {
            background: var(--success, #4ade80);
        }
        .volume-bar-fill.goal-pending {
            background: var(--accent);
        }
        .goal-indicator {
            font-size: 9px;
            color: var(--text-dim);
            text-align: right;
            margin-top: 4px;
        }
        .workout-item-stats {
            display: flex;
            gap: 16px;
            margin-bottom: 10px;
            font-size: 12px;
            color: var(--text-dim);
        }
        .workout-item-stat {
            display: flex;
            align-items: center;
            gap: 4px;
        }
        .muscle-tags {
            display: flex;
            gap: 6px;
            flex-wrap: wrap;
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
        .stats-summary {
            background: var(--surface);
            border: 1px solid var(--border);
            border-radius: 8px;
            padding: 16px;
            margin-bottom: 20px;
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 16px;
        }
        .stat-box {
            text-align: center;
        }
        .stat-value {
            font-family: 'Space Mono', monospace;
            font-size: 20px;
            font-weight: 700;
            color: var(--accent);
        }
        .stat-label {
            font-size: 10px;
            color: var(--text-dim);
            text-transform: uppercase;
            margin-top: 4px;
        }
        .goal-box {
            background: var(--surface);
            border: 1px solid var(--border);
            border-radius: 8px;
            padding: 12px 16px;
            margin-bottom: 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .goal-label {
            font-size: 11px;
            color: var(--text-dim);
            text-transform: uppercase;
        }
        .goal-value {
            font-family: 'Space Mono', monospace;
            font-size: 16px;
            font-weight: 700;
        }
        .goal-value.met {
            color: var(--success, #4ade80);
        }
        .goal-value.pending {
            color: var(--accent);
        }
    </style>
</head>
<body>
    <div class="app">
        <div class="content">
            <h1 style="margin-bottom: 24px; font-size: 20px; text-transform: uppercase; letter-spacing: 1px;">WORKOUT HISTORY</h1>
            
            <div class="goal-box">
                <div class="goal-label">GOAL: 20K per workout</div>
                <div class="goal-value <?php echo count($workoutsAtGoal) > 0 ? 'met' : 'pending'; ?>">
                    <?php echo count($workoutsAtGoal); ?>/<?php echo $totalWorkouts; ?> HIT
                </div>
            </div>
            
            <div class="stats-summary">
                <div class="stat-box">
                    <div class="stat-value"><?php echo $totalWorkouts; ?></div>
                    <div class="stat-label">Total Workouts</div>
                </div>
                <div class="stat-box">
                    <div class="stat-value"><?php echo number_format($maxVolume); ?></div>
                    <div class="stat-label">Best Volume</div>
                </div>
                <div class="stat-box">
                    <div class="stat-value"><?php echo number_format($avgVolume, 0); ?></div>
                    <div class="stat-label">Avg Volume</div>
                </div>
            </div>
            
            <?php if (empty($workouts)): ?>
                <div class="empty-state">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                        <path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"/>
                    </svg>
                    <p>No workouts yet. Start your first one!</p>
                </div>
            <?php else: ?>
                <div class="workout-list">
                    <?php foreach ($workouts as $workout): 
                        $exercises = getWorkoutExercises($db, $workout['id']);
                        $muscleGroups = getMuscleGroups($exercises);
                        $timestamp = $workout['started_at'] / 1000;
                        $volumePercent = min(($workout['total_volume'] / $GOAL_VOLUME) * 100, 100);
                        $goalMet = $workout['total_volume'] >= $GOAL_VOLUME;
                    ?>
                        <a href="/workouts/view?id=<?php echo $workout['id']; ?>" class="workout-item">
                            <div class="workout-item-header">
                                <div>
                                    <div class="workout-item-title">Workout #<?php echo $workout['id']; ?> — <?php echo date('l', $timestamp); ?></div>
                                    <div class="workout-item-date"><?php echo date('M j, Y', $timestamp); ?></div>
                                </div>
                                <div class="volume-badge"><?php echo number_format($workout['total_volume']); ?> lbs</div>
                            </div>
                            
                            <div class="volume-bar-bg">
                                <div class="volume-bar-fill <?php echo $goalMet ? 'goal-met' : 'goal-pending'; ?>" style="width: <?php echo $volumePercent; ?>%"></div>
                            </div>
                            <div class="goal-indicator">
                                <?php if ($goalMet): ?>
                                    ✓ 20K GOAL MET
                                <?php else: ?>
                                    <?php echo number_format($GOAL_VOLUME - $workout['total_volume']); ?> lbs to 20K
                                <?php endif; ?>
                            </div>
                            
                            <div class="workout-item-stats">
                                <span class="workout-item-stat">
                                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                        <path d="M6 5v14M18 5v14M3 8h18M3 16h18"/>
                                    </svg>
                                    <?php echo $workout['set_count']; ?> sets
                                </span>
                                <?php if ($workout['total_volume'] > 0 && $workout['set_count'] > 0): ?>
                                <span class="workout-item-stat">
                                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                        <circle cx="12" cy="12" r="10"/>
                                        <path d="M12 6v6l4 2"/>
                                    </svg>
                                    <?php echo number_format($workout['total_volume'] / $workout['set_count'], 0); ?> lbs/set
                                </span>
                                <?php endif; ?>
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
        
        <nav>
            <a href="/dashboard" class="nav-btn">
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
            <a href="/workouts" class="nav-btn active">
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
