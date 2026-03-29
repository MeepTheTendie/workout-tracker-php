<?php
/**
 * Database Function Tests
 * 
 * Tests all database wrapper functions with a test database.
 * 
 * @package WorkoutTracker\Tests
 */

declare(strict_types=1);

use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/../includes/database.php';

/**
 * @covers \getDB
 * @covers \dbQuery
 * @covers \dbFetchOne
 * @covers \dbFetchAll
 * @covers \dbInsert
 * @covers \dbUpdate
 * @covers \dbDelete
 * @covers \dbBegin
 * @covers \dbCommit
 * @covers \dbRollback
 * @covers \dbInTransaction
 * @covers \dbTransaction
 */
class DatabaseTest extends TestCase
{
    private static PDO $testDb;
    
    /**
     * Set up test database before running tests
     */
    public static function setUpBeforeClass(): void
    {
        // Use SQLite for testing
        $_ENV['DB_HOST'] = '';
        $_ENV['DB_NAME'] = ':memory:';
        $_ENV['DB_USER'] = '';
        $_ENV['DB_PASS'] = '';
        
        // Create test schema
        $db = getDB();
        $db->exec('
            CREATE TABLE test_users (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                name TEXT NOT NULL,
                email TEXT UNIQUE NOT NULL,
                age INTEGER DEFAULT 0
            )
        ');
    }
    
    /**
     * Clean up test data after each test
     */
    protected function tearDown(): void
    {
        // Clean up test data
        try {
            dbQuery('DELETE FROM test_users WHERE email LIKE ?', ['test_%@example.com']);
        } catch (Exception $e) {
            // Table might not exist, ignore
        }
    }
    
    /**
     * Test that getDB returns a valid PDO connection
     */
    public function testGetDBReturnsPDO(): void
    {
        $db = getDB();
        $this->assertInstanceOf(PDO::class, $db);
    }
    
    /**
     * Test that getDB returns the same connection (singleton)
     */
    public function testGetDBReturnsSameInstance(): void
    {
        $db1 = getDB();
        $db2 = getDB();
        $this->assertSame($db1, $db2);
    }
    
    /**
     * Test dbInsert creates a new record
     */
    public function testDbInsertCreatesRecord(): void
    {
        $id = dbInsert('test_users', [
            'name' => 'Test User',
            'email' => 'test_insert@example.com',
            'age' => 25
        ]);
        
        $this->assertIsInt($id);
        $this->assertGreaterThan(0, $id);
        
        // Verify record exists
        $user = dbFetchOne('SELECT * FROM test_users WHERE id = ?', [$id]);
        $this->assertNotNull($user);
        $this->assertEquals('Test User', $user['name']);
    }
    
    /**
     * Test dbInsert with empty data throws exception
     */
    public function testDbInsertEmptyDataThrowsException(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Cannot insert empty data');
        
        dbInsert('test_users', []);
    }
    
    /**
     * Test dbFetchOne returns a single row
     */
    public function testDbFetchOneReturnsSingleRow(): void
    {
        dbInsert('test_users', [
            'name' => 'Fetch Test',
            'email' => 'test_fetch@example.com',
            'age' => 30
        ]);
        
        $user = dbFetchOne('SELECT * FROM test_users WHERE email = ?', ['test_fetch@example.com']);
        
        $this->assertIsArray($user);
        $this->assertEquals('Fetch Test', $user['name']);
        $this->assertEquals(30, $user['age']);
    }
    
    /**
     * Test dbFetchOne returns null for no results
     */
    public function testDbFetchOneReturnsNullForNoResults(): void
    {
        $user = dbFetchOne('SELECT * FROM test_users WHERE email = ?', ['nonexistent@example.com']);
        $this->assertNull($user);
    }
    
    /**
     * Test dbFetchAll returns all matching rows
     */
    public function testDbFetchAllReturnsAllRows(): void
    {
        dbInsert('test_users', ['name' => 'User A', 'email' => 'test_a@example.com', 'age' => 20]);
        dbInsert('test_users', ['name' => 'User B', 'email' => 'test_b@example.com', 'age' => 25]);
        dbInsert('test_users', ['name' => 'User C', 'email' => 'test_c@example.com', 'age' => 30]);
        
        $users = dbFetchAll('SELECT * FROM test_users WHERE email LIKE ? ORDER BY age', ['test_%@example.com']);
        
        $this->assertIsArray($users);
        $this->assertCount(3, $users);
        $this->assertEquals('User A', $users[0]['name']);
        $this->assertEquals('User C', $users[2]['name']);
    }
    
    /**
     * Test dbFetchAll returns empty array for no results
     */
    public function testDbFetchAllReturnsEmptyArrayForNoResults(): void
    {
        $users = dbFetchAll('SELECT * FROM test_users WHERE email = ?', ['nonexistent@example.com']);
        $this->assertIsArray($users);
        $this->assertEmpty($users);
    }
    
    /**
     * Test dbUpdate modifies records
     */
    public function testDbUpdateModifiesRecords(): void
    {
        $id = dbInsert('test_users', [
            'name' => 'Update Test',
            'email' => 'test_update@example.com',
            'age' => 20
        ]);
        
        $affected = dbUpdate('test_users', 
            ['name' => 'Updated Name', 'age' => 25],
            'id = ?',
            [$id]
        );
        
        $this->assertEquals(1, $affected);
        
        $user = dbFetchOne('SELECT * FROM test_users WHERE id = ?', [$id]);
        $this->assertEquals('Updated Name', $user['name']);
        $this->assertEquals(25, $user['age']);
    }
    
    /**
     * Test dbUpdate with empty data throws exception
     */
    public function testDbUpdateEmptyDataThrowsException(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Cannot update with empty data');
        
        dbUpdate('test_users', [], 'id = 1', []);
    }
    
    /**
     * Test dbUpdate returns 0 for no matching rows
     */
    public function testDbUpdateReturnsZeroForNoMatches(): void
    {
        $affected = dbUpdate('test_users', 
            ['name' => 'No Match'],
            'id = ?',
            [999999]
        );
        
        $this->assertEquals(0, $affected);
    }
    
    /**
     * Test dbDelete removes records
     */
    public function testDbDeleteRemovesRecords(): void
    {
        $id = dbInsert('test_users', [
            'name' => 'Delete Test',
            'email' => 'test_delete@example.com',
            'age' => 20
        ]);
        
        $deleted = dbDelete('test_users', 'id = ?', [$id]);
        
        $this->assertEquals(1, $deleted);
        
        $user = dbFetchOne('SELECT * FROM test_users WHERE id = ?', [$id]);
        $this->assertNull($user);
    }
    
    /**
     * Test dbDelete returns 0 for no matching rows
     */
    public function testDbDeleteReturnsZeroForNoMatches(): void
    {
        $deleted = dbDelete('test_users', 'id = ?', [999999]);
        $this->assertEquals(0, $deleted);
    }
    
    /**
     * Test transaction commit
     */
    public function testTransactionCommit(): void
    {
        dbBegin();
        
        $id = dbInsert('test_users', [
            'name' => 'Transaction Test',
            'email' => 'test_transaction@example.com',
            'age' => 20
        ]);
        
        dbCommit();
        
        // Verify record exists after commit
        $user = dbFetchOne('SELECT * FROM test_users WHERE id = ?', [$id]);
        $this->assertNotNull($user);
    }
    
    /**
     * Test transaction rollback
     */
    public function testTransactionRollback(): void
    {
        dbBegin();
        
        $id = dbInsert('test_users', [
            'name' => 'Rollback Test',
            'email' => 'test_rollback@example.com',
            'age' => 20
        ]);
        
        dbRollback();
        
        // Verify record does NOT exist after rollback
        $user = dbFetchOne('SELECT * FROM test_users WHERE id = ?', [$id]);
        $this->assertNull($user);
    }
    
    /**
     * Test dbInTransaction returns correct state
     */
    public function testDbInTransaction(): void
    {
        $this->assertFalse(dbInTransaction());
        
        dbBegin();
        $this->assertTrue(dbInTransaction());
        
        dbRollback();
        $this->assertFalse(dbInTransaction());
    }
    
    /**
     * Test dbTransaction helper commits on success
     */
    public function testDbTransactionCommitsOnSuccess(): void
    {
        $id = null;
        
        $result = dbTransaction(function() use (&$id) {
            $id = dbInsert('test_users', [
                'name' => 'Transaction Helper',
                'email' => 'test_helper@example.com',
                'age' => 25
            ]);
            return 'success';
        });
        
        $this->assertEquals('success', $result);
        
        // Verify record exists
        $user = dbFetchOne('SELECT * FROM test_users WHERE id = ?', [$id]);
        $this->assertNotNull($user);
    }
    
    /**
     * Test dbTransaction helper rolls back on exception
     */
    public function testDbTransactionRollsBackOnException(): void
    {
        $id = null;
        
        try {
            dbTransaction(function() use (&$id) {
                $id = dbInsert('test_users', [
                    'name' => 'Should Rollback',
                    'email' => 'test_rollback_helper@example.com',
                    'age' => 25
                ]);
                
                throw new Exception('Intentional error');
            });
        } catch (Exception $e) {
            // Expected
        }
        
        // Verify record does NOT exist
        $user = dbFetchOne('SELECT * FROM test_users WHERE id = ?', [$id]);
        $this->assertNull($user);
    }
    
    /**
     * Test parameterized query prevents SQL injection
     */
    public function testParameterizedQueryPreventsInjection(): void
    {
        $maliciousEmail = "test' OR '1'='1";
        
        dbInsert('test_users', [
            'name' => 'Security Test',
            'email' => 'legitimate@example.com',
            'age' => 20
        ]);
        
        // This should return null, not all users
        $user = dbFetchOne('SELECT * FROM test_users WHERE email = ?', [$maliciousEmail]);
        $this->assertNull($user);
    }
}
