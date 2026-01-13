<?php
/**
 * Create Database Script
 * Creates the database if it doesn't exist
 */

require_once __DIR__ . '/../includes/config.php';

$host = env('DB_HOST', 'localhost');
$port = env('DB_PORT', '5432');
$database = env('DB_NAME', 'mekanfotografcisi');
$username = env('DB_USER', 'postgres');
$password = env('DB_PASSWORD', '');

// Connect to PostgreSQL server (not specific database)
$dsn = sprintf("pgsql:host=%s;port=%s", $host, $port);

try {
    $pdo = new PDO($dsn, $username, $password, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
    ]);
    
    // Check if database exists
    $stmt = $pdo->query("SELECT 1 FROM pg_database WHERE datname = '$database'");
    $exists = $stmt->fetch();
    
    if (!$exists) {
        // Create database
        $pdo->exec("CREATE DATABASE \"$database\"");
        echo "✓ Database '$database' created successfully\n";
    } else {
        echo "⊘ Database '$database' already exists\n";
    }
    
    echo "\nYou can now run migrations:\n";
    echo "  php scripts/migrate.php\n";
    
} catch (PDOException $e) {
    echo "✗ Error: " . $e->getMessage() . "\n";
    echo "\nMake sure:\n";
    echo "  1. PostgreSQL is running\n";
    echo "  2. Connection details in .env are correct\n";
    echo "  3. User '$username' has permission to create databases\n";
    exit(1);
}


