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
        error_log("Migrations table create error: " . $e->getMessage());
    }

    // 2. Scan for migration files
    $migrationDir = __DIR__ . '/migrations';
    if (!is_dir($migrationDir))
        return;

    $files = glob($migrationDir . '/*.sql');
    sort($files);

    foreach ($files as $file) {
        $filename = basename($file);

        // 3. Check if already executed
        $check = $db->query("SELECT id FROM migrations WHERE filename = ?", [$filename]);

        if (empty($check)) {
            if (php_sapi_name() === 'cli')
                echo "Executing migration: $filename...\n";
            $sqlContent = file_get_contents($file);

            try {
                // Split by custom marker -- STATEMENT
                // This is the most reliable way when dealing with HTML/JS content
                $statements = preg_split('/-- STATEMENT/i', $sqlContent);

                foreach ($statements as $statement) {
                    $statement = trim($statement);
                    if (!empty($statement)) {
                        $db->query($statement);
                    }
                }

                // 4. Mark as executed
                $db->insert('migrations', ['filename' => $filename]);
                if (php_sapi_name() === 'cli')
                    echo "Migration $filename successfully executed.\n";
            } catch (Exception $e) {
                if (php_sapi_name() === 'cli')
                    echo "ERROR in migration $filename: " . $e->getMessage() . "\n";
                error_log("Migration error ($filename): " . $e->getMessage());
                break;
            }
        }
    }
}

if (php_sapi_name() === 'cli' && basename(__FILE__) === basename($_SERVER['PHP_SELF'])) {
    run_migrations();
}
