<?php
if (function_exists('opcache_invalidate')) {
    opcache_invalidate('/var/www/workout-tracker-lamp/pages/workouts/view.php', true);
}
echo 'Cache cleared';
