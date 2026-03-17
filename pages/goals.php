<?php
requireAuth();
$db = getDB();

// Get user's goals
$stmt = $db->prepare("SELECT * FROM goals WHERE user_id = ? ORDER BY created_at DESC");
$stmt->execute([$_SESSION['user_id']]);
$goals = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Goals - Workout Tracker</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Space+Mono:wght@400;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="/css/style.css">
    <style>
        .section-card { background: var(--surface); border: 1px solid var(--border); border-radius: 8px; padding: 16px; margin-bottom: 16px; }
        .goal-item { background: #fff; border-radius: 4px; padding: 16px; margin-bottom: 12px; }
        .goal-title { font-size: 14px; font-weight: 700; color: #1a1a1a; margin-bottom: 8px; }
        .goal-progress { background: #eee; border-radius: 3px; height: 6px; margin: 8px 0; }
        .goal-progress-fill { background: var(--accent); height: 100%; border-radius: 3px; }
        .goal-stats { display: flex; justify-content: space-between; font-size: 12px; color: #666; }
    </style>
</head>
<body>
    <div class="app">
        <div class="content">
            <h1 style="font-size: 20px; text-transform: uppercase; letter-spacing: 1px; margin-bottom: 24px;">GOALS</h1>
            
            <?php if (empty($goals)): ?>
                <div class="section-card">
                    <p style="color: var(--text-dim);">No goals set yet.</p>
                </div>
            <?php else: ?>
                <?php foreach ($goals as $goal): 
                    $progress = $goal['target_value'] > 0 ? ($goal['current_value'] / $goal['target_value'] * 100) : 0;
                    $progress = min(100, max(0, $progress));
                ?>
                    <div class="goal-item">
                        <div class="goal-title"><?php echo h($goal['title']); ?></div>
                        <div class="goal-progress">
                            <div class="goal-progress-fill" style="width: <?php echo $progress; ?>%"></div>
                        </div>
                        <div class="goal-stats">
                            <span><?php echo $goal['current_value']; ?> <?php echo h($goal['unit']); ?></span>
                            <span><?php echo $goal['target_value']; ?> <?php echo h($goal['unit']); ?></span>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
        
        <nav>
            <a href="/dashboard" class="nav-btn">Home</a>
            <a href="/workouts/create" class="nav-btn">Log</a>
            <a href="/workouts" class="nav-btn">History</a>
            <a href="/stats" class="nav-btn">Stats</a>
            <a href="/prs" class="nav-btn">PRs</a>
        </nav>
    </div>
</body>
</html>
