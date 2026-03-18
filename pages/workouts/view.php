<?php
// Aggressive cache prevention
header('Cache-Control: no-store, no-cache, must-revalidate, proxy-revalidate, max-age=0');
header('Pragma: no-cache');
header('Expires: -1');

requireAuth();
$db = getDB();

$workoutId = intval($_GET['id'] ?? 0);

// Get workout details
$stmt = $db->prepare("SELECT w.* FROM workouts w WHERE w.id = ? AND w.user_id = ?");
$stmt->execute([$workoutId, $_SESSION['user_id']]);
$workout = $stmt->fetch();

if (!$workout) {
    header('Location: /workouts');
    exit;
}

// Get sets for this workout
$stmt = $db->prepare("SELECT ws.*, e.name as exercise_name FROM workout_sets ws JOIN exercises e ON ws.exercise_id = e.id WHERE ws.workout_id = ? ORDER BY ws.id");
$stmt->execute([$workoutId]);
$sets = $stmt->fetchAll();

// Group by exercise
$exercises = [];
$totalVolume = 0;
foreach ($sets as $set) {
    $exName = $set['exercise_name'] ?? 'Unknown';
    if (!isset($exercises[$exName])) {
        $exercises[$exName] = ['sets' => [], 'volume' => 0];
    }
    $setVolume = floatval($set['weight']) * intval($set['reps']);
    $exercises[$exName]['sets'][] = array_merge($set, ['volume' => $setVolume]);
    $exercises[$exName]['volume'] += $setVolume;
    $totalVolume += $setVolume;
}

$timestamp = $workout['started_at'] / 1000;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="Cache-Control" content="no-store">
    <title>Workout #<?php echo $workoutId; ?> - <?php echo date('M j, Y', $timestamp); ?></title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Space+Mono:wght@400;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="/css/style.css?v=<?php echo time(); ?>">
    <style>
        .back-link { color: var(--text-dim); text-decoration: none; display: inline-flex; align-items: center; gap: 8px; margin-bottom: 16px; }
        .back-link:hover { color: var(--accent); }
        .workout-date { color: var(--text-dim); font-size: 12px; margin-top: 4px; }
        .workout-total { color: var(--accent); font-size: 14px; font-weight: 700; margin-top: 8px; }
        .exercise-section { background: #fff; border-radius: 4px; padding: 16px; margin-bottom: 12px; }
        .exercise-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 12px; }
        .exercise-name { font-size: 14px; font-weight: 700; color: #1a1a1a; text-transform: uppercase; }
        .exercise-volume { font-size: 12px; color: var(--accent); font-weight: 700; }
        .sets-table { width: 100%; }
        .sets-table td { padding: 8px 0; border-bottom: 1px solid #eee; color: #666; font-size: 13px; }
        .sets-table td:last-child { text-align: right; }
        .sets-table tr:last-child td { border-bottom: none; }
        .set-num { color: var(--accent); font-weight: 700; }
        .set-volume { color: #999; font-size: 11px; }
    </style>
</head>
<body>
    <div class="app">
        <div class="content">
            <a href="/workouts" class="back-link">← Back to History</a>
            
            <h1 style="font-size: 20px; text-transform: uppercase; letter-spacing: 1px;">WORKOUT #<?php echo $workoutId; ?></h1>
            <p class="workout-date"><?php echo date('l, M j, Y', $timestamp); ?></p>
            <p class="workout-total">Total Volume: <?php echo number_format($totalVolume); ?> lbs</p>
            
            <?php if (empty($exercises)): ?>
                <p style="color: var(--text-dim); margin-top: 20px;">No exercises recorded for this workout.</p>
            <?php else: ?>
                <?php foreach ($exercises as $exName => $data): ?>
                    <div class="exercise-section">
                        <div class="exercise-header">
                            <div class="exercise-name"><?php echo htmlspecialchars($exName, ENT_QUOTES, 'UTF-8'); ?></div>
                            <div class="exercise-volume"><?php echo number_format($data['volume']); ?> lbs</div>
                        </div>
                        <table class="sets-table">
                            <?php foreach ($data['sets'] as $i => $set): ?>
                                <tr>
                                    <td><span class="set-num">#<?php echo $i + 1; ?></span></td>
                                    <td><?php echo intval($set['reps']); ?> reps</td>
                                    <td><?php echo $set['weight']; ?> lbs</td>
                                    <td class="set-volume"><?php echo number_format($set['volume']); ?> lbs</td>
                                </tr>
                            <?php endforeach; ?>
                        </table>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
        
        <nav>
            <a href="/dashboard" class="nav-btn">Home</a>
            <a href="/workouts/create" class="nav-btn">Log</a>
            <a href="/workouts" class="nav-btn active">History</a>
            <a href="/stats" class="nav-btn">Stats</a>
            <a href="/prs" class="nav-btn">PRs</a>
        </nav>
    </div>
</body>
</html>
