<?php
/**
 * PHPUnit Bootstrap
 */

// Mock session functions if not available
if (!function_exists('session_start')) {
    function session_start(): bool {
        return true;
    }
    function session_destroy(): bool {
        return true;
    }
    function session_regenerate_id(bool $delete_old_session = false): bool {
        return true;
    }
}

// Mock header function
if (!function_exists('header')) {
    function header(string $header, bool $replace = true, int $response_code = 0): void {
        // No-op for testing
    }
}

// Mock header_remove function
if (!function_exists('header_remove')) {
    function header_remove(?string $name = null): void {
        // No-op for testing
    }
}

// Define test constants
define('TEST_ROOT', dirname(__DIR__));

// Autoloader
spl_autoload_register(function ($class) {
    $file = TEST_ROOT . '/includes/' . strtolower(str_replace('_', '-', $class)) . '.php';
    if (file_exists($file)) {
        require_once $file;
    }
});

// Load test helpers
require_once TEST_ROOT . '/tests/TestHelpers.php';
