<?php
/**
 * Authentication Functions
 */

/**
 * Check if user is logged in
 */
function isLoggedIn(): bool
{
    return isset($_SESSION['user_id']) && $_SESSION['user_id'] > 0;
}

/**
 * Require authentication, redirect to login if not
 */
function requireAuth(): void
{
    if (!isLoggedIn()) {
        $_SESSION['redirect_after_login'] = $_SERVER['REQUEST_URI'];
        redirect('/login');
    }
}

/**
 * Get current user ID
 */
function currentUserId(): int
{
    return $_SESSION['user_id'] ?? 0;
}

/**
 * Get current user data
 */
function currentUser(): ?array
{
    if (!isLoggedIn()) {
        return null;
    }
    
    return dbFetchOne(
        "SELECT id, name, email, created_at FROM users WHERE id = ?",
        [currentUserId()]
    );
}

/**
 * Attempt login
 */
function attemptLogin(string $password): bool
{
    $hash = $_ENV['ADMIN_PASSWORD_HASH'] ?? '';
    
    if (password_verify($password, $hash)) {
        // Find existing user by email or use first user
        $user = dbFetchOne("SELECT id FROM users WHERE id = 1");
        
        if (!$user) {
            $user = dbFetchOne("SELECT id FROM users LIMIT 1");
        }
        
        if (!$user) {
            // Create default user
            $now = time() * 1000;
            $userId = dbInsert('users', [
                'name' => 'Meep',
                'email' => 'meep@workout.local',
                'password' => $hash,
                'created_at' => $now,
                'updated_at' => $now
            ]);
        } else {
            $userId = $user['id'];
        }
        
        $_SESSION['user_id'] = $userId;
        $_SESSION['created'] = time();
        
        return true;
    }
    
    return false;
}

/**
 * Logout user
 */
function logout(): void
{
    session_destroy();
}

/**
 * Check if user owns a resource
 */
function ownsResource(string $table, int $id, string $userColumn = 'user_id'): bool
{
    $record = dbFetchOne(
        "SELECT $userColumn FROM $table WHERE id = ?",
        [$id]
    );
    
    return $record && $record[$userColumn] == currentUserId();
}
