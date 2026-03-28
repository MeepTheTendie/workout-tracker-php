CREATE TABLE IF NOT EXISTS cardio_sessions (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id BIGINT UNSIGNED NOT NULL,
    session_type VARCHAR(50) NOT NULL DEFAULT 'bike',
    duration_minutes INT NOT NULL DEFAULT 15,
    calories_burned INT DEFAULT NULL,
    heart_rate_avg INT DEFAULT NULL,
    resistance_level INT DEFAULT NULL,
    notes TEXT DEFAULT NULL,
    completed_at BIGINT UNSIGNED NOT NULL,
    created_at BIGINT UNSIGNED NOT NULL,
    updated_at BIGINT UNSIGNED NOT NULL,
    INDEX idx_user_completed (user_id, completed_at)
);
