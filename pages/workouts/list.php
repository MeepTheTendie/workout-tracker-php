<?php
requireAuth();
$db = getDB();

// Get all workouts
$stmt = $db->prepare("SELECT w.*, COUNT(ws.id) as set_count FROM workouts w LEFT JOIN workout_sets ws ON w.id = ws.workout_id WHERE w.user_id = ? AND w.ended_at IS NOT NULL GROUP BY w.id ORDER BY w.started_at DESC");
$stmt->execute([$_SESSION['user_id']]);
$workouts = $stmt->fetchAll();

function getWorkoutExercises($db, $workoutId) {
    $stmt = $db->prepare("SELECT DISTINCT e.name FROM workout_sets ws JOIN exercises e ON ws.exercise_id = e.id WHERE ws.workout_id = ?");
    $stmt->execute([$workoutId]);
    return $stmt->fetchAll(PDO::FETCH_COLUMN);
}

function getMuscleGroups($exercises) {
    $groups = [];
    foreach ($exercises as $name) {
        $nameLower = strtolower($name);
        if (str_contains($nameLower, 'press') || str_contains($nameLower, 'fly') || str_contains($nameLower, 'chest') || str_contains($nameLower, 'pec')) {
            $groups[] = 'chest';
        } elseif (str_contains($nameLower, 'row') || str_contains($nameLower, 'pull') || str_contains($nameLower, 'lat') || str_contains($nameLower, 'back') || str_contains($nameLower, 'extension')) {
            $groups[] = 'back';
        } elseif (str_contains($nameLower, 'squat') || str_contains($nameLower, 'leg') || str_contains($nameLower, 'calf') || str_contains($nameLower, 'hip')) {
            $groups[] = 'leg';
        } elseif (str_contains($nameLower, 'shoulder') || str_contains($nameLower, 'raise') || str_contains($nameLower, 'shrug')) {
            $groups[] = 'shoulders';
        } elseif (str_contains($nameLower, 'curl') || str_contains($nameLower, 'bicep') || str_contains($nameLower, 'tricep')) {
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
    <title>Workout History - Workout Tracker</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Space+Mono:wght@400;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="/css/style.css">
</head>
<body>
    <div class="app">
        <div class="content">
            <h1 style="margin-bottom: 24px; font-size: 20px; text-transform: uppercase; letter-spacing: 1px;">WORKOUT HISTORY</h1>
            
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
                    ?>
                        <a href="/workouts/view?id=<?php echo $workout['id']; ?>" class="workout-item">
                            <div class="workout-item-header">
                                <div>
                                    <div class="workout-item-title">Workout #<?php echo $workout['id']; ?> — <?php echo date('l', $timestamp); ?></div>
                                    <div class="workout-item-date"><?php echo date('M j, Y', $timestamp); ?></div>
                                </div>
                            </div>
                            <div class="workout-item-stats">
                                <span class="workout-item-stat">
                                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                        <path d="M6 5v14M18 5v14M3 8h18M3 16h18"/>
                                    </svg>
                                    <?php echo $workout['set_count']; ?> sets
                                </span>
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
