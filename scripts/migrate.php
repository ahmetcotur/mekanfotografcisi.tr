<?php
/**
 * Database Migration Script
 * Runs all migration files in order
 */

require_once __DIR__ . '/../includes/database.php';

$migrationsDir = __DIR__ . '/../supabase/migrations';
$migrationFiles = glob($migrationsDir . '/*.sql');

// Sort files by name (001, 002, etc.)
sort($migrationFiles);

echo "Starting database migrations...\n\n";

$db = new DatabaseClient();
$connection = $db->getConnection();

// Create migrations tracking table
try {
    $connection->exec("
        CREATE TABLE IF NOT EXISTS schema_migrations (
            version VARCHAR(255) PRIMARY KEY,
            applied_at TIMESTAMP WITH TIME ZONE DEFAULT NOW()
        )
    ");
    echo "✓ Migration tracking table created\n";
} catch (PDOException $e) {
    echo "⚠ Migration tracking table already exists\n";
}

$applied = 0;
$skipped = 0;

foreach ($migrationFiles as $file) {
    $version = basename($file);
    
    // Check if already applied
    $check = $connection->prepare("SELECT version FROM schema_migrations WHERE version = ?");
    $check->execute([$version]);
    
    if ($check->fetch()) {
        echo "⊘ Skipping: $version (already applied)\n";
        $skipped++;
        continue;
    }
    
    echo "→ Running: $version\n";
    
    $sql = file_get_contents($file);
    
    try {
        $connection->beginTransaction();
        
        // Execute entire SQL file at once (PostgreSQL supports this)
        $connection->exec($sql);
        
        // Mark as applied
        $insert = $connection->prepare("INSERT INTO schema_migrations (version) VALUES (?)");
        $insert->execute([$version]);
        
        $connection->commit();
        echo "  ✓ Successfully applied: $version\n";
        $applied++;
    } catch (PDOException $e) {
        $connection->rollBack();
        echo "  ✗ Error applying $version: " . $e->getMessage() . "\n";
        echo "  Stopping migrations.\n";
        exit(1);
    }
}

echo "\n";
echo "Migration complete!\n";
echo "  Applied: $applied\n";
echo "  Skipped: $skipped\n";
echo "  Total: " . count($migrationFiles) . "\n";

