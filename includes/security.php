<?php
/**
 * Security Utilities
 * CSRF protection, input sanitization, validation
 */

/**
 * Generate or return existing CSRF token
 */
function csrfToken(): string
{
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

/**
 * Get HTML hidden input for CSRF token
 */
function csrfField(): string
{
    $token = csrfToken();
    return '<input type="hidden" name="csrf_token" value="' . htmlspecialchars($token, ENT_QUOTES, 'UTF-8') . '">';
}

/**
 * Validate CSRF token from request
 */
function validateCsrf(): bool
{
    $token = $_POST['csrf_token'] ?? '';
    return !empty($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}

/**
 * Require valid CSRF token or die
 */
function requireCsrf(): void
{
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && !validateCsrf()) {
        http_response_code(403);
        die('Invalid security token. Please refresh the page and try again.');
    }
}

/**
 * Sanitize integer input
 */
function intParam($value, int $default = 0, int $min = PHP_INT_MIN, int $max = PHP_INT_MAX): int
{
    $int = filter_var($value, FILTER_VALIDATE_INT);
    if ($int === false) {
        return $default;
    }
    return max($min, min($max, $int));
}

/**
 * Sanitize float input
 */
function floatParam($value, float $default = 0.0, float $min = -PHP_FLOAT_MAX, float $max = PHP_FLOAT_MAX): float
{
    $float = filter_var($value, FILTER_VALIDATE_FLOAT);
    if ($float === false) {
        return $default;
    }
    return max($min, min($max, $float));
}

/**
 * Sanitize string input
 */
function stringParam($value, string $default = ''): string
{
    if (!is_string($value)) {
        return $default;
    }
    return trim($value);
}

/**
 * HTML escape
 */
function e(string $text): string
{
    return htmlspecialchars($text, ENT_QUOTES, 'UTF-8');
}

/**
 * Redirect with optional flash message
 */
function redirect(string $url, string $message = null, string $type = 'success'): void
{
    if ($message !== null) {
        $_SESSION['flash'] = ['message' => $message, 'type' => $type];
    }
    header("Location: $url");
    exit;
}

/**
 * Get and clear flash message
 */
function getFlash(): ?array
{
    if (isset($_SESSION['flash'])) {
        $flash = $_SESSION['flash'];
        unset($_SESSION['flash']);
        return $flash;
    }
    return null;
}

/**
 * Rate limit check (simple in-memory)
 */
function checkRateLimit(string $key, int $maxAttempts = 60, int $windowSeconds = 60): bool
{
    $now = time();
    $ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
    $rateKey = "rate_limit_{$key}_{$ip}";
    
    if (!isset($_SESSION[$rateKey])) {
        $_SESSION[$rateKey] = ['count' => 1, 'start' => $now];
        return true;
    }
    
    $data = $_SESSION[$rateKey];
    
    // Reset if window expired
    if ($now - $data['start'] > $windowSeconds) {
        $_SESSION[$rateKey] = ['count' => 1, 'start' => $now];
        return true;
    }
    
    // Check limit
    if ($data['count'] >= $maxAttempts) {
        return false;
    }
    
    $_SESSION[$rateKey]['count']++;
    return true;
}
