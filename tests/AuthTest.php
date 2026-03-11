<?php

use PHPUnit\Framework\TestCase;

class AuthTest extends TestCase
{
    protected function setUp(): void
    {
        // Start fresh session for each test
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        $_SESSION = [];
    }

    public function testLoginWithCorrectPassword()
    {
        // Mock getPassword to return test password
        $result = login('GrrMeep#5Dude');
        $this->assertTrue($result);
        $this->assertTrue(isLoggedIn());
    }

    public function testLoginWithWrongPassword()
    {
        $result = login('wrongpassword');
        $this->assertFalse($result);
        $this->assertFalse(isLoggedIn());
    }

    public function testLogout()
    {
        login('GrrMeep#5Dude');
        $this->assertTrue(isLoggedIn());
        
        logout();
        $this->assertFalse(isLoggedIn());
    }

    public function testCsrfTokenIsGenerated()
    {
        $token = csrfToken();
        $this->assertNotEmpty($token);
        $this->assertEquals(64, strlen($token)); // 32 bytes = 64 hex chars
    }

    public function testCsrfTokenValidation()
    {
        $token = csrfToken();
        $this->assertTrue(validateCsrfToken($token));
        $this->assertFalse(validateCsrfToken('invalid_token'));
    }
}
