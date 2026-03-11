<?php
// Bootstrap for workout-tracker tests

define('DB_FILE', __DIR__ . '/../data/workout.db');
define('DATABASE_URL', 'sqlite:' . DB_FILE);

require_once __DIR__ . '/../lib/Database.php';
require_once __DIR__ . '/../config.php';
