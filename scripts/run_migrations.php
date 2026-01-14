<?php
/**
 * Simple Database Migration Runner
 */
require_once __DIR__ . '/../includes/database.php';

function run_migrations()
{
    $db = new DatabaseClient();

    // 1. Create migrations tracking table if not exists
    try {
        $db->query("
            CREATE TABLE IF NOT EXISTS migrations (
                id SERIAL PRIMARY KEY,
                filename VARCHAR(255) UNIQUE NOT NULL,
                executed_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
            )
        ");
    } catch (Exception $e) {
        // Table might exist or error, log but continue
        error_log("Migrations table create error: " . $e->getMessage());
    }

    // 2. Scan for migration files
    $migrationDir = __DIR__ . '/migrations';
    if (!is_dir($migrationDir))
        return;

    $files = glob($migrationDir . '/*.sql');
    sort($files); // Run in order

    foreach ($files as $file) {
        $filename = basename($file);

        // 3. Check if already executed
        $check = $db->query("SELECT id FROM migrations WHERE filename = ?", [$filename]);

        if (empty($check)) {
            echo "Executing migration: $filename...\n";
            $sql = file_get_contents($file);

            try {
                // Split multi-statement SQL files simple way
                // In production with complex SQL, this might need better parsing
                $db->query($sql);

                // 4. Mark as executed
                $db->insert('migrations', ['filename' => $filename]);
                echo "Migration $filename successfully executed.\n";
            } catch (Exception $e) {
                echo "ERROR in migration $filename: " . $e->getMessage() . "\n";
                error_log("Migration error ($filename): " . $e->getMessage());
                // Stop to prevent further data issues
                break;
            }
        }
    }
}

// Only if run directly from CLI
if (php_sapi_name() === 'cli' && basename(__FILE__) === basename($_SERVER['PHP_SELF'])) {
    run_migrations();
}
