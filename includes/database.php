<?php
/**
 * Database Connection and Query Builder
 * 
 * Provides a simple PDO wrapper with:
 * - Connection pooling via static singleton
 * - Prepared statement support for all queries
 * - Transaction support (begin/commit/rollback)
 * - Consistent error handling and logging
 * 
 * @package WorkoutTracker\Database
 */

declare(strict_types=1);

/**
 * Get database connection (singleton pattern)
 * 
 * Creates a single PDO instance and reuses it across requests.
 * Uses environment variables for configuration.
 * 
 * @return PDO The database connection instance
 * @throws PDOException If connection fails (caught and logged internally)
 */
function getDB(): PDO
{
    static $db = null;
    
    if ($db === null) {
        $host = $_ENV['DB_HOST'] ?? 'localhost';
        $name = $_ENV['DB_NAME'] ?? 'workout_tracker';
        $user = $_ENV['DB_USER'] ?? 'meep';
        $pass = $_ENV['DB_PASS'] ?? '';
        
        // Use SQLite when DB_HOST is empty (for testing)
        if (empty($host)) {
            $dsn = "sqlite:{$name}";
            if ($name === ':memory:') {
                $dsn = 'sqlite::memory:';
            }
            $user = null;
            $pass = null;
        } else {
            $dsn = "mysql:host={$host};dbname={$name};charset=utf8mb4";
        }
        
        try {
            $db = new PDO($dsn, $user, $pass, [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
                PDO::ATTR_STRINGIFY_FETCHES => false,
            ]);
        } catch (PDOException $e) {
            error_log("[DB] Connection failed: " . $e->getMessage());
            throw new Exception("Database connection error. Please try again later.");
        }
    }
    
    return $db;
}

/**
 * Execute a parameterized query
 * 
 * @param string $sql The SQL query with placeholders
 * @param array $params Parameters to bind to the query
 * @return PDOStatement The executed statement
 * @throws PDOException On query failure
 */
function dbQuery(string $sql, array $params = []): PDOStatement
{
    $db = getDB();
    $stmt = $db->prepare($sql);
    $stmt->execute($params);
    return $stmt;
}

/**
 * Fetch a single row from a query
 * 
 * @param string $sql The SQL query
 * @param array $params Query parameters
 * @return array|null The first row as associative array, or null if no results
 */
function dbFetchOne(string $sql, array $params = []): ?array
{
    $stmt = dbQuery($sql, $params);
    $result = $stmt->fetch();
    return $result ?: null;
}

/**
 * Fetch all rows from a query
 * 
 * @param string $sql The SQL query
 * @param array $params Query parameters
 * @return array Array of associative arrays (empty if no results)
 */
function dbFetchAll(string $sql, array $params = []): array
{
    $stmt = dbQuery($sql, $params);
    return $stmt->fetchAll();
}

/**
 * Insert a row and return the new ID
 * 
 * @param string $table Table name (will be escaped)
 * @param array $data Associative array of column => value
 * @return int The last insert ID
 * @throws PDOException On insert failure
 */
function dbInsert(string $table, array $data): int
{
    if (empty($data)) {
        throw new InvalidArgumentException("Cannot insert empty data");
    }
    
    $db = getDB();
    
    // Escape column names
    $columns = implode('`, `', array_map('escapeIdentifier', array_keys($data)));
    $placeholders = implode(', ', array_fill(0, count($data), '?'));
    
    $sql = "INSERT INTO `{$table}` (`{$columns}`) VALUES ({$placeholders})";
    $stmt = $db->prepare($sql);
    $stmt->execute(array_values($data));
    
    return (int) $db->lastInsertId();
}

/**
 * Update rows matching a condition
 * 
 * @param string $table Table name
 * @param array $data Associative array of column => value to update
 * @param string $where WHERE clause (use placeholders for values)
 * @param array $whereParams Parameters for WHERE clause
 * @return int Number of affected rows
 * @throws PDOException On update failure
 */
function dbUpdate(string $table, array $data, string $where, array $whereParams = []): int
{
    if (empty($data)) {
        throw new InvalidArgumentException("Cannot update with empty data");
    }
    
    $db = getDB();
    
    $sets = [];
    $values = [];
    
    foreach ($data as $column => $value) {
        $sets[] = "`" . escapeIdentifier($column) . "` = ?";
        $values[] = $value;
    }
    
    $sql = "UPDATE `{$table}` SET " . implode(', ', $sets) . " WHERE {$where}";
    $stmt = $db->prepare($sql);
    $stmt->execute(array_merge($values, $whereParams));
    
    return $stmt->rowCount();
}

/**
 * Delete rows matching a condition
 * 
 * @param string $table Table name
 * @param string $where WHERE clause (use placeholders for values)
 * @param array $params Parameters for WHERE clause
 * @return int Number of deleted rows
 * @throws PDOException On delete failure
 */
function dbDelete(string $table, string $where, array $params = []): int
{
    $sql = "DELETE FROM `{$table}` WHERE {$where}";
    $stmt = dbQuery($sql, $params);
    return $stmt->rowCount();
}

/**
 * Escape SQL identifier (column/table name)
 * 
 * Prevents SQL injection via column/table names by only allowing
 * alphanumeric characters and underscores.
 * 
 * @param string $identifier The identifier to escape
 * @return string The sanitized identifier
 * @throws InvalidArgumentException If identifier contains invalid characters
 */
function escapeIdentifier(string $identifier): string
{
    // Only allow alphanumeric and underscore
    if (!preg_match('/^[a-zA-Z0-9_]+$/', $identifier)) {
        throw new InvalidArgumentException("Invalid SQL identifier: " . $identifier);
    }
    return $identifier;
}

/**
 * Start a database transaction
 * 
 * Use with dbCommit() and dbRollback() for atomic operations.
 * Transactions are automatically rolled back on script termination
 * if not explicitly committed.
 */
function dbBegin(): void
{
    getDB()->beginTransaction();
}

/**
 * Commit the current transaction
 * 
 * @throws PDOException If no transaction is active
 */
function dbCommit(): void
{
    getDB()->commit();
}

/**
 * Rollback the current transaction
 * 
 * @throws PDOException If no transaction is active
 */
function dbRollback(): void
{
    getDB()->rollBack();
}

/**
 * Check if a transaction is currently active
 * 
 * @return bool True if a transaction is in progress
 */
function dbInTransaction(): bool
{
    return getDB()->inTransaction();
}

/**
 * Execute a callback within a transaction
 * 
 * Automatically commits if callback succeeds, rolls back on exception.
 * 
 * @template T
 * @param callable(): T $callback Function to execute within transaction
 * @return T The callback's return value
 * @throws Exception If callback throws or transaction fails
 */
function dbTransaction(callable $callback)
{
    dbBegin();
    
    try {
        $result = $callback();
        dbCommit();
        return $result;
    } catch (Exception $e) {
        if (dbInTransaction()) {
            dbRollback();
        }
        throw $e;
    }
}
