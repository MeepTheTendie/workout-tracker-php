<?php
/**
 * Add Cardio Session Action
 */

requireCsrf();

$userId = currentUserId();
$sessionType = $_POST['session_type'] ?? 'bike';
$duration = intParam($_POST['duration_minutes'] ?? 15);
$calories = intParam($_POST['calories_burned'] ?? 0);
$resistance = intParam($_POST['resistance_level'] ?? 0);
$notes = trim($_POST['notes'] ?? '');

if ($duration <= 0 || $duration > 120) {
    redirect('/cardio', 'Invalid duration', 'error');
}

$now = now();

// Check if this would be a duplicate (same day, same AM/PM)
$today = strtotime('today') * 1000;
$tomorrow = strtotime('tomorrow') * 1000;
$existing = dbFetchAll(
    "SELECT * FROM cardio_sessions 
     WHERE user_id = ? AND completed_at >= ? AND completed_at < ? AND notes = ?",
    [$userId, $today, $tomorrow, $notes]
);

if (!empty($existing)) {
    redirect('/cardio', ucfirst(strtolower($notes)) . ' already logged today!', 'error');
}

dbInsert('cardio_sessions', [
    'user_id' => $userId,
    'session_type' => $sessionType,
    'duration_minutes' => $duration,
    'calories_burned' => $calories > 0 ? $calories : null,
    'resistance_level' => $resistance > 0 ? $resistance : null,
    'notes' => $notes,
    'completed_at' => $now,
    'created_at' => $now,
    'updated_at' => $now
]);

redirect('/cardio', 'Cardio session logged! Keep it up!');
