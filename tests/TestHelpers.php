<?php
/**
 * Test Helper Functions
 */

// Mock database functions for unit testing
class MockDB {
    private static $data = [];
    private static $lastId = 0;
    
    public static function reset(): void {
        self::$data = [];
        self::$lastId = 0;
    }
    
    public static function setData(string $table, array $data): void {
        self::$data[$table] = $data;
    }
    
    public static function getData(string $table): array {
        return self::$data[$table] ?? [];
    }
    
    public static function getLastId(): int {
        return ++self::$lastId;
    }
}

// Mock environment
$_ENV = array_merge($_ENV ?? [], [
    'APP_ENV' => 'testing',
    'DB_HOST' => 'localhost',
    'DB_NAME' => 'test_db',
    'DB_USER' => 'test_user',
    'DB_PASS' => 'test_pass',
    'SESSION_NAME' => 'test_session',
    'SESSION_LIFETIME' => '3600',
    'ADMIN_PASSWORD_HASH' => password_hash('testpassword', PASSWORD_DEFAULT),
]);

// Initialize empty session
$_SESSION = [];

/**
 * Assertion helper for validation errors
 */
function expectValidationError(callable $callback, string $expectedMessage): void {
    try {
        $callback();
        PHPUnit\Framework\Assert::fail('Expected ValidationError was not thrown');
    } catch (ValidationError $e) {
        PHPUnit\Framework\Assert::assertStringContainsString($expectedMessage, $e->getMessage());
    }
}
