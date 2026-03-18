<?php
/**
 * Workout Tracker - Security Library
 * Provides CSRF protection, rate limiting, and input validation
 */

class Security {
    private const RATE_LIMIT_DIR = __DIR__ . '/../storage/rate_limits';
    private const MAX_REQUESTS = 100;        // Max requests per window
    private const RATE_WINDOW = 60;          // Window in seconds (1 minute)
    private const MAX_LOGIN_ATTEMPTS = 5;    // Max login attempts per IP
    private const LOGIN_BLOCK_TIME = 900;    // Block time in seconds (15 minutes)
    
    /**
     * Initialize rate limiting directory
     */
    public static function init(): void {
        if (!is_dir(self::RATE_LIMIT_DIR)) {
            mkdir(self::RATE_LIMIT_DIR, 0750, true);
        }
    }
    
    /**
     * Generate and store CSRF token
     */
    public static function generateCsrfToken(): string {
        if (empty($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }
        return $_SESSION['csrf_token'];
    }
    
    /**
     * Validate CSRF token
     */
    public static function validateCsrfToken(string $token): bool {
        if (empty($_SESSION['csrf_token'])) {
            return false;
        }
        return hash_equals($_SESSION['csrf_token'], $token);
    }
    
    /**
     * Get CSRF token field HTML
     */
    public static function csrfField(): string {
        $token = self::generateCsrfToken();
        return '<input type="hidden" name="csrf_token" value="' . htmlspecialchars($token) . '">';
    }
    
    /**
     * Get CSRF token for JavaScript
     */
    public static function csrfToken(): string {
        return self::generateCsrfToken();
    }
    
    /**
     * Check rate limit for current IP
     */
    public static function checkRateLimit(string $action = 'default'): bool {
        self::init();
        
        $ip = self::getClientIp();
        $key = md5($ip . '_' . $action);
        $file = self::RATE_LIMIT_DIR . '/' . $key . '.json';
        
        $now = time();
        $data = ['count' => 0, 'first_request' => $now, 'blocked_until' => 0];
        
        if (file_exists($file)) {
            $content = file_get_contents($file);
            $data = json_decode($content, true) ?: $data;
        }
        
        // Check if blocked
        if ($data['blocked_until'] > $now) {
            return false;
        }
        
        // Reset window if expired
        if ($now - $data['first_request'] > self::RATE_WINDOW) {
            $data = ['count' => 0, 'first_request' => $now, 'blocked_until' => 0];
        }
        
        // Increment count
        $data['count']++;
        
        // Check limit
        $limit = ($action === 'login') ? self::MAX_LOGIN_ATTEMPTS : self::MAX_REQUESTS;
        if ($data['count'] > $limit) {
            $data['blocked_until'] = $now + (($action === 'login') ? self::LOGIN_BLOCK_TIME : self::RATE_WINDOW);
            file_put_contents($file, json_encode($data), LOCK_EX);
            return false;
        }
        
        file_put_contents($file, json_encode($data), LOCK_EX);
        return true;
    }
    
    /**
     * Get remaining rate limit for response headers
     */
    public static function getRateLimitHeaders(string $action = 'default'): array {
        $ip = self::getClientIp();
        $key = md5($ip . '_' . $action);
        $file = self::RATE_LIMIT_DIR . '/' . $key . '.json';
        
        $limit = ($action === 'login') ? self::MAX_LOGIN_ATTEMPTS : self::MAX_REQUESTS;
        $remaining = $limit;
        $reset = self::RATE_WINDOW;
        
        if (file_exists($file)) {
            $data = json_decode(file_get_contents($file), true);
            if ($data) {
                $remaining = max(0, $limit - $data['count']);
                $reset = max(0, self::RATE_WINDOW - (time() - $data['first_request']));
            }
        }
        
        return [
            'X-RateLimit-Limit' => $limit,
            'X-RateLimit-Remaining' => $remaining,
            'X-RateLimit-Reset' => $reset
        ];
    }
    
    /**
     * Sanitize integer input
     */
    public static function sanitizeInt($value, int $default = 0, int $min = null, int $max = null): int {
        $value = filter_var($value, FILTER_VALIDATE_INT);
        if ($value === false) {
            return $default;
        }
        if ($min !== null && $value < $min) {
            return $min;
        }
        if ($max !== null && $value > $max) {
            return $max;
        }
        return $value;
    }
    
    /**
     * Sanitize float input
     */
    public static function sanitizeFloat($value, float $default = 0, float $min = null, float $max = null): float {
        $value = filter_var($value, FILTER_VALIDATE_FLOAT);
        if ($value === false) {
            return $default;
        }
        if ($min !== null && $value < $min) {
            return $min;
        }
        if ($max !== null && $value > $max) {
            return $max;
        }
        return $value;
    }
    
    /**
     * Sanitize string input
     */
    public static function sanitizeString($value, int $maxLength = 255): string {
        if (!is_string($value)) {
            return '';
        }
        $value = trim($value);
        $value = substr($value, 0, $maxLength);
        return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
    }
    
    /**
     * Sanitize text input (allows more characters)
     */
    public static function sanitizeText($value, int $maxLength = 1000): string {
        if (!is_string($value)) {
            return '';
        }
        $value = trim($value);
        $value = substr($value, 0, $maxLength);
        return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
    }
    
    /**
     * Get client IP address
     */
    public static function getClientIp(): string {
        $headers = ['HTTP_CF_CONNECTING_IP', 'HTTP_X_FORWARDED_FOR', 'REMOTE_ADDR'];
        foreach ($headers as $header) {
            if (!empty($_SERVER[$header])) {
                $ips = explode(',', $_SERVER[$header]);
                $ip = trim($ips[0]);
                if (filter_var($ip, FILTER_VALIDATE_IP)) {
                    return $ip;
                }
            }
        }
        return '127.0.0.1';
    }
    
    /**
     * Apply rate limit headers
     */
    public static function applyRateLimitHeaders(string $action = 'default'): void {
        foreach (self::getRateLimitHeaders($action) as $header => $value) {
            header("$header: $value");
        }
    }
    
    /**
     * Send rate limit exceeded response
     */
    public static function rateLimitExceeded(): void {
        http_response_code(429);
        header('Content-Type: application/json');
        echo json_encode(['error' => 'Rate limit exceeded. Please try again later.']);
        exit;
    }
    
    /**
     * Send CSRF error response
     */
    public static function csrfError(): void {
        http_response_code(403);
        header('Content-Type: application/json');
        echo json_encode(['error' => 'Invalid CSRF token']);
        exit;
    }
    
    /**
     * Validate file path (prevent path traversal)
     */
    public static function validatePath(string $path, string $baseDir): ?string {
        // Remove null bytes
        $path = str_replace("\0", '', $path);
        
        // Normalize path
        $realBase = realpath($baseDir);
        $realPath = realpath($baseDir . '/' . $path);
        
        if ($realPath === false) {
            return null;
        }
        
        // Ensure path is within base directory
        if (strpos($realPath, $realBase) !== 0) {
            return null;
        }
        
        return $realPath;
    }
    
    /**
     * Apply security headers
     */
    public static function applySecurityHeaders(): void {
        header('X-Frame-Options: DENY');
        header('X-Content-Type-Options: nosniff');
        header('Referrer-Policy: strict-origin-when-cross-origin');
        header("Content-Security-Policy: default-src 'self'; img-src 'self' data:; style-src 'self' 'unsafe-inline' https://fonts.googleapis.com; font-src 'self' https://fonts.gstatic.com; script-src 'self' 'unsafe-inline'; connect-src 'self';");
        header('X-XSS-Protection: 1; mode=block');
        header('Permissions-Policy: geolocation=(), microphone=(), camera=(), payment=(), usb=(), magnetometer=(), gyroscope=(), payment=()');
        header('Strict-Transport-Security: max-age=31536000; includeSubDomains; preload');
        
        // Remove PHP version info
        header_remove('X-Powered-By');
    }
}
