-- Database Optimizations for Workout Tracker v2
-- Run this to add performance indexes

USE workout_tracker;

-- Index for fetching user's workouts (used in dashboard, history)
CREATE INDEX IF NOT EXISTS idx_workouts_user_ended_started 
ON workouts(user_id, ended_at, started_at DESC);

-- Index for active workout lookups
CREATE INDEX IF NOT EXISTS idx_workouts_user_active 
ON workouts(user_id, ended_at) WHERE ended_at IS NULL;

-- Index for workout sets lookups
CREATE INDEX IF NOT EXISTS idx_workout_sets_workout_completed 
ON workout_sets(workout_id, completed_at);

-- Index for exercise lookups in sets
CREATE INDEX IF NOT EXISTS idx_workout_sets_exercise 
ON workout_sets(exercise_id);

-- Index for cardio sessions
CREATE INDEX IF NOT EXISTS idx_cardio_user_completed 
ON cardio_sessions(user_id, completed_at);

-- Index for body composition scans
CREATE INDEX IF NOT EXISTS idx_bodycomp_user_date 
ON body_composition_scans(user_id, scan_date);

-- Index for exercise categories
CREATE INDEX IF NOT EXISTS idx_exercises_category 
ON exercises(category);

-- Analyze tables for query optimizer
ANALYZE TABLE workouts;
ANALYZE TABLE workout_sets;
ANALYZE TABLE exercises;
ANALYZE TABLE cardio_sessions;
ANALYZE TABLE body_composition_scans;
