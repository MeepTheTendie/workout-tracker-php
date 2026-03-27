<?php
/**
 * Database Connection
 * Simple PDO wrapper with consistent configuration
 */

function getDB(): PDO
{
    static $db = null;
    
    if ($db === null) {
        $host = $_ENV['DB_HOST'] ?? 'localhost';
        $name = $_ENV['DB_NAME'] ?? 'workout_tracker';
        $user = $_ENV['DB_USER'] ?? 'meep';
        $pass = $_ENV['DB_PASS'] ?? '';
        
        $dsn = "mysql:host=$host;dbname=$name;charset=utf8mb4";
        
        try {
            $db = new PDO($dsn, $user, $pass, [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
            ]);
        } catch (PDOException $e) {
            error_log("Database connection failed: " . $e->getMessage());
            die("Database connection error. Please try again later.");
        }
    }
    
    return $db;
}

/**
 * Execute a query with optional parameters
 */
function dbQuery(string $sql, array $params = []): PDOStatement
{
    $db = getDB();
    $stmt = $db->prepare($sql);
    $stmt->execute($params);
    return $stmt;
}

/**
 * Fetch a single row
 */
function dbFetchOne(string $sql, array $params = []): ?array
{
    $stmt = dbQuery($sql, $params);
    $result = $stmt->fetch();
    return $result ?: null;
}

/**
 * Fetch all rows
 */
function dbFetchAll(string $sql, array $params = []): array
{
    $stmt = dbQuery($sql, $params);
    return $stmt->fetchAll();
}

/**
 * Insert and return last ID
 */
function dbInsert(string $table, array $data): int
{
    $db = getDB();
    
    $columns = implode('`, `', array_keys($data));
    $placeholders = implode(', ', array_fill(0, count($data), '?'));
    
    $sql = "INSERT INTO `$table` (`$columns`) VALUES ($placeholders)";
    $stmt = $db->prepare($sql);
    $stmt->execute(array_values($data));
    
    return (int)$db->lastInsertId();
}

/**
 * Update rows
 */
function dbUpdate(string $table, array $data, string $where, array $whereParams = []): int
{
    $db = getDB();
    
    $sets = [];
    $values = [];
    
    foreach ($data as $column => $value) {
        $sets[] = "`$column` = ?";
        $values[] = $value;
    }
    
    $sql = "UPDATE `$table` SET " . implode(', ', $sets) . " WHERE $where";
    $stmt = $db->prepare($sql);
    $stmt->execute(array_merge($values, $whereParams));
    
    return $stmt->rowCount();
}

/**
 * Delete rows
 */
function dbDelete(string $table, string $where, array $params = []): int
{
    $sql = "DELETE FROM `$table` WHERE $where";
    $stmt = dbQuery($sql, $params);
    return $stmt->rowCount();
}

/**
 * Start transaction
 */
function dbBegin(): void
{
    getDB()->beginTransaction();
}

/**
 * Commit transaction
 */
function dbCommit(): void
{
    getDB()->commit();
}

/**
 * Rollback transaction
 */
function dbRollback(): void
{
    getDB()->rollBack();
}
