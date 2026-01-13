<?php
/**
 * PostgreSQL Database Client
 * Direct database connection for mekanfotografcisi.tr
 */

require_once __DIR__ . '/config.php';

class DatabaseClient
{
    private $connection;
    private $host;
    private $port;
    private $database;
    private $username;
    private $password;

    public function __construct()
    {
        $this->host = env('DB_HOST', 'localhost');
        $this->port = env('DB_PORT', '5432');
        $this->database = env('DB_NAME', 'mekanfotografcisi');
        $this->username = env('DB_USER', 'postgres');
        $this->password = env('DB_PASSWORD', '');

        $this->connect();
    }

    private function connect()
    {
        $dsn = sprintf(
            "pgsql:host=%s;port=%s;dbname=%s",
            $this->host,
            $this->port,
            $this->database
        );

        try {
            $this->connection = new PDO($dsn, $this->username, $this->password, [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
            ]);
        } catch (PDOException $e) {
            error_log("Database connection failed: " . $e->getMessage());
            throw new Exception("Database connection failed. Please check your configuration.");
        }
    }

    /**
     * Select data from a table
     * Supports Supabase-style parameters for compatibility
     */
    public function select($table, $params = [])
    {
        // Build SELECT clause
        $selectFields = '*';
        if (isset($params['select'])) {
            $selectFields = $params['select'];
            unset($params['select']);
        }

        $sql = "SELECT " . $selectFields . " FROM " . $this->quoteIdentifier($table);
        $conditions = [];
        $values = [];

        // Build WHERE clause from params
        foreach ($params as $key => $value) {
            if (strpos($key, '.') !== false) {
                // Handle operators like 'eq.', 'like.', etc.
                list($operator, $column) = explode('.', $key, 2);

                switch ($operator) {
                    case 'eq':
                        // Handle boolean values
                        if ($value === 'true' || $value === true) {
                            $conditions[] = $this->quoteIdentifier($column) . " = true";
                        } elseif ($value === 'false' || $value === false) {
                            $conditions[] = $this->quoteIdentifier($column) . " = false";
                        } else {
                            // Remove 'eq.' prefix if present in value
                            $cleanValue = is_string($value) && strpos($value, 'eq.') === 0 ? substr($value, 3) : $value;
                            $conditions[] = $this->quoteIdentifier($column) . " = ?";
                            $values[] = $cleanValue;
                        }
                        break;
                    case 'ne':
                        $conditions[] = $this->quoteIdentifier($column) . " != ?";
                        $values[] = $value;
                        break;
                    case 'like':
                    case 'ilike':
                        $conditions[] = $this->quoteIdentifier($column) . " ILIKE ?";
                        $values[] = '%' . str_replace(['%', '_'], ['\%', '\_'], $value) . '%';
                        break;
                    case 'gt':
                        $conditions[] = $this->quoteIdentifier($column) . " > ?";
                        $values[] = $value;
                        break;
                    case 'lt':
                        $conditions[] = $this->quoteIdentifier($column) . " < ?";
                        $values[] = $value;
                        break;
                    case 'in':
                        if (is_array($value)) {
                            $placeholders = implode(',', array_fill(0, count($value), '?'));
                            $conditions[] = $this->quoteIdentifier($column) . " IN ($placeholders)";
                            $values = array_merge($values, $value);
                        }
                        break;
                }
            } elseif ($key === 'order') {
                // Handle ordering - will be added after WHERE
                continue;
            } elseif ($key === 'limit') {
                // Handle limit - will be added at the end
                continue;
            } elseif ($key === 'offset') {
                // Handle offset - will be added at the end
                continue;
            } else {
                // Direct column = value
                $conditions[] = $this->quoteIdentifier($key) . " = ?";
                $values[] = $value;
            }
        }

        if (!empty($conditions)) {
            $sql .= " WHERE " . implode(" AND ", $conditions);
        }

        // Add ORDER BY
        if (isset($params['order'])) {
            $sql .= " ORDER BY " . $params['order'];
        }

        // Add LIMIT and OFFSET
        if (isset($params['limit'])) {
            $sql .= " LIMIT " . (int) $params['limit'];
        }
        if (isset($params['offset'])) {
            $sql .= " OFFSET " . (int) $params['offset'];
        }

        try {
            $stmt = $this->connection->prepare($sql);
            $stmt->execute($values);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Database query failed: " . $e->getMessage() . " | SQL: " . $sql);
            throw $e; // Re-throw to allow fallback to mock data
        }
    }

    /**
     * Insert data into a table
     */
    public function insert($table, $data)
    {
        $columns = array_keys($data);
        $placeholders = array_fill(0, count($columns), '?');
        $values = [];
        foreach ($data as $value) {
            if (is_bool($value)) {
                $values[] = $value ? 'true' : 'false';
            } else {
                $values[] = $value;
            }
        }

        $sql = sprintf(
            "INSERT INTO %s (%s) VALUES (%s) RETURNING *",
            $this->quoteIdentifier($table),
            implode(', ', array_map([$this, 'quoteIdentifier'], $columns)),
            implode(', ', $placeholders)
        );

        try {
            $stmt = $this->connection->prepare($sql);
            $stmt->execute($values);
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Database insert failed: " . $e->getMessage());
            throw new Exception("Failed to insert data: " . $e->getMessage());
        }
    }

    /**
     * Update data in a table
     */
    public function update($table, $data, $where)
    {
        $setClause = [];
        $values = [];

        foreach ($data as $column => $value) {
            // Handle boolean values for PostgreSQL
            if (is_bool($value)) {
                $setClause[] = $this->quoteIdentifier($column) . " = ?";
                $values[] = $value ? 'true' : 'false';
                continue;
            }

            // Handle JSONB fields - if value is a JSON string, cast it to JSONB
            if (is_string($value) && (strlen($value) > 1) && (strpos($value, '[') === 0 || strpos($value, '{') === 0)) {
                // Try to decode to check if it's valid JSON
                $decoded = json_decode($value, true);
                if (json_last_error() === JSON_ERROR_NONE) {
                    // It's valid JSON, cast to JSONB in SQL
                    $setClause[] = $this->quoteIdentifier($column) . " = ?::jsonb";
                    $values[] = $value;
                } else {
                    // Not valid JSON, treat as regular string
                    $setClause[] = $this->quoteIdentifier($column) . " = ?";
                    $values[] = $value;
                }
            } else {
                $setClause[] = $this->quoteIdentifier($column) . " = ?";
                $values[] = $value;
            }
        }

        $whereClause = [];
        foreach ($where as $column => $value) {
            $whereClause[] = $this->quoteIdentifier($column) . " = ?";
            $values[] = $value;
        }

        $sql = sprintf(
            "UPDATE %s SET %s WHERE %s RETURNING *",
            $this->quoteIdentifier($table),
            implode(', ', $setClause),
            implode(' AND ', $whereClause)
        );

        try {
            $stmt = $this->connection->prepare($sql);
            $stmt->execute($values);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Database update failed: " . $e->getMessage());
            throw new Exception("Failed to update data: " . $e->getMessage());
        }
    }

    /**
     * Delete data from a table
     */
    public function delete($table, $where)
    {
        $whereClause = [];
        $values = [];

        foreach ($where as $column => $value) {
            $whereClause[] = $this->quoteIdentifier($column) . " = ?";
            $values[] = $value;
        }

        $sql = sprintf(
            "DELETE FROM %s WHERE %s RETURNING *",
            $this->quoteIdentifier($table),
            implode(' AND ', $whereClause)
        );

        try {
            $stmt = $this->connection->prepare($sql);
            $stmt->execute($values);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Database delete failed: " . $e->getMessage());
            throw new Exception("Failed to delete data: " . $e->getMessage());
        }
    }

    /**
     * Execute raw SQL query
     */
    public function query($sql, $params = [])
    {
        try {
            $stmt = $this->connection->prepare($sql);
            $stmt->execute($params);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Database query failed: " . $e->getMessage());
            throw new Exception("Query failed: " . $e->getMessage());
        }
    }

    /**
     * Quote identifier (table/column name)
     */
    private function quoteIdentifier($identifier)
    {
        return '"' . str_replace('"', '""', $identifier) . '"';
    }

    /**
     * Get connection for advanced operations
     */
    public function getConnection()
    {
        return $this->connection;
    }
}

// Global instance will be created when needed
$db = null;

