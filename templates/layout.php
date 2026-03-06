<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>WORKOUT TRACKER</title>
    <link rel="stylesheet" href="/style.css">
    <link href="https://fonts.googleapis.com/css2?family=Space+Mono:wght@400;700&display=swap" rel="stylesheet">
</head>
<body>
    <div class="app">
        <div class="content">
            <?php include __DIR__ . '/templates/' . $page . '.php'; ?>
        </div>
        
        <nav>
            <a href="/?page=index" class="nav-btn <?= $page === 'index' ? 'active' : '' ?>">HOME</a>
            <a href="/?page=workout" class="nav-btn <?= $page === 'workout' ? 'active' : '' ?>">LOG</a>
            <a href="/?page=history" class="nav-btn <?= $page === 'history' ? 'active' : '' ?>">HISTORY</a>
            <a href="/?page=stats" class="nav-btn <?= $page === 'stats' ? 'active' : '' ?>">STATS</a>
            <a href="/?page=goals" class="nav-btn <?= $page === 'goals' ? 'active' : '' ?>">PRS</a>
        </nav>
    </div>
</body>
</html>
