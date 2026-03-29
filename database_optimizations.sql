-- Database Optimizations for Workout Tracker v2
-- Run this to add performance indexes

USE workout_tracker;

-- Index for fetching user's workouts (used in dashboard, history)
-- Note: Run these one by one. If an index already exists, you'll get an error - that's fine.

ALTER TABLE workouts ADD INDEX idx_workouts_user_ended_started (user_id, ended_at, started_at DESC);
ALTER TABLE workout_sets ADD INDEX idx_workout_sets_workout_completed (workout_id, completed_at);
ALTER TABLE workout_sets ADD INDEX idx_workout_sets_exercise (exercise_id);
ALTER TABLE cardio_sessions ADD INDEX idx_cardio_user_completed (user_id, completed_at);
ALTER TABLE body_composition_scans ADD INDEX idx_bodycomp_user_date (user_id, scan_date);
ALTER TABLE exercises ADD INDEX idx_exercises_category (category);

-- Analyze tables for query optimizer
ANALYZE TABLE workouts;
ANALYZE TABLE workout_sets;
ANALYZE TABLE exercises;
ANALYZE TABLE cardio_sessions;
ANALYZE TABLE body_composition_scans;
