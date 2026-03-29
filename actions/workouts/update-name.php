<?php
/**
 * Update Workout Name Action
 */

requireCsrf();

$userId = currentUserId();

// Get active workout
$workout = dbFetchOne(
    "SELECT id FROM workouts WHERE user_id = ? AND ended_at IS NULL",
    [$userId]
);

if (!$workout) {
    redirect('/workouts/log', 'No active workout', 'error');
}

// Get new name
$name = isset($_POST['workout_name']) ? trim($_POST['workout_name']) : '';

// Update workout name
$db = getDB();
$stmt = $db->prepare("UPDATE workouts SET notes = ?, updated_at = ? WHERE id = ?");
$stmt->execute([$name, now(), $workout['id']]);

redirect('/workouts/log', 'Workout name updated');
