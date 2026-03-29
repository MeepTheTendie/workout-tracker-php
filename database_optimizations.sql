-- Database Optimizations for Workout Tracker v2
-- Run this to add performance indexes

USE workout_tracker;

-- Drop existing indexes if they exist (to avoid errors)
DROP INDEX IF EXISTS idx_workouts_user_ended_started ON workouts;
DROP INDEX IF EXISTS idx_workout_sets_workout_completed ON workout_sets;
DROP INDEX IF EXISTS idx_workout_sets_exercise ON workout_sets;
DROP INDEX IF EXISTS idx_cardio_user_completed ON cardio_sessions;
DROP INDEX IF EXISTS idx_bodycomp_user_date ON body_composition_scans;
DROP INDEX IF EXISTS idx_exercises_category ON exercises;

-- Index for fetching user's workouts (used in dashboard, history)
ALTER TABLE workouts ADD INDEX idx_workouts_user_ended_started (user_id, ended_at, started_at DESC);

-- Index for workout sets lookups
ALTER TABLE workout_sets ADD INDEX idx_workout_sets_workout_completed (workout_id, completed_at);

-- Index for exercise lookups in sets
ALTER TABLE workout_sets ADD INDEX idx_workout_sets_exercise (exercise_id);

-- Index for cardio sessions
ALTER TABLE cardio_sessions ADD INDEX idx_cardio_user_completed (user_id, completed_at);

-- Index for body composition scans
ALTER TABLE body_composition_scans ADD INDEX idx_bodycomp_user_date (user_id, scan_date);

-- Index for exercise categories
ALTER TABLE exercises ADD INDEX idx_exercises_category (category);

-- Analyze tables for query optimizer
ANALYZE TABLE workouts;
ANALYZE TABLE workout_sets;
ANALYZE TABLE exercises;
ANALYZE TABLE cardio_sessions;
ANALYZE TABLE body_composition_scans;
