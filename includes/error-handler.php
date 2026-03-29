<?php
/**
 * Error Handler
 * 
 * Centralized error handling for the application
 */

/**
 * Custom error handler
 */
function handleError($errno, $errstr, $errfile, $errline)
{
    // Log error
    error_log("Error [$errno]: $errstr in $errfile on line $errline");
    
    // Don't display errors in production
    if (($_ENV['APP_ENV'] ?? 'production') === 'production') {
        return true;
    }
    
    // Display error in development
    echo "<div style='background: #ff4444; color: white; padding: 10px; margin: 10px; border-radius: 5px;'>";
    echo "<strong>Error:</strong> $errstr<br>";
    echo "<small>File: $errfile, Line: $errline</small>";
    echo "</div>";
    
    return true;
}

/**
 * Exception handler
 */
function handleException($exception)
{
    $message = $exception->getMessage();
    $file = $exception->getFile();
    $line = $exception->getLine();
    
    // Log exception
    error_log("Exception: $message in $file on line $line");
    error_log($exception->getTraceAsString());
    
    http_response_code(500);
    
    if (($_ENV['APP_ENV'] ?? 'production') === 'production') {
        echo "<h1>Something went wrong</h1>";
        echo "<p>Please try again later.</p>";
    } else {
        echo "<h1>Exception</h1>";
        echo "<p><strong>$message</strong></p>";
        echo "<p>File: $file, Line: $line</p>";
        echo "<pre>" . $exception->getTraceAsString() . "</pre>";
    }
}

/**
 * Shutdown handler for fatal errors
 */
function handleShutdown()
{
    $error = error_get_last();
    if ($error && in_array($error['type'], [E_ERROR, E_CORE_ERROR, E_COMPILE_ERROR, E_PARSE])) {
        error_log("Fatal Error: {$error['message']} in {$error['file']} on line {$error['line']}");
        
        http_response_code(500);
        
        if (($_ENV['APP_ENV'] ?? 'production') === 'production') {
            echo "<h1>Something went wrong</h1>";
            echo "<p>Please try again later.</p>";
        } else {
            echo "<h1>Fatal Error</h1>";
            echo "<p>{$error['message']}</p>";
            echo "<p>File: {$error['file']}, Line: {$error['line']}</p>";
        }
    }
}

// Register handlers
set_error_handler('handleError');
set_exception_handler('handleException');
register_shutdown_function('handleShutdown');

/**
 * Safe JSON decode with error handling
 */
function safeJsonDecode(string $json, bool $assoc = true)
{
    $data = json_decode($json, $assoc);
    if (json_last_error() !== JSON_ERROR_NONE) {
        error_log("JSON decode error: " . json_last_error_msg());
        return null;
    }
    return $data;
}

/**
 * Validate that required keys exist in array
 */
function validateRequired(array $data, array $required): array
{
    $missing = [];
    foreach ($required as $key) {
        if (!isset($data[$key]) || (is_string($data[$key]) && trim($data[$key]) === '')) {
            $missing[] = $key;
        }
    }
    return $missing;
}

/**
 * Log application event
 */
function logEvent(string $type, string $message, array $context = []): void
{
    $logDir = __DIR__ . '/../storage/logs';
    if (!is_dir($logDir)) {
        mkdir($logDir, 0755, true);
    }
    
    $logFile = $logDir . '/' . date('Y-m-d') . '.log';
    $timestamp = date('Y-m-d H:i:s');
    $contextStr = empty($context) ? '' : ' ' . json_encode($context);
    
    $line = "[$timestamp] [$type] $message$contextStr" . PHP_EOL;
    file_put_contents($logFile, $line, FILE_APPEND | LOCK_EX);
}
