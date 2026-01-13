<?php
/**
 * Run Migration Helper
 * Applies the 007_wordpress_style_schema.sql file to the database.
 */

require_once __DIR__ . '/includes/database.php';

$db = new DatabaseClient();
$sqlFile = __DIR__ . '/supabase/migrations/007_wordpress_style_schema.sql';

if (!file_exists($sqlFile)) {
    die("Migration file not found: $sqlFile\n");
}

$sql = file_get_contents($sqlFile);

echo "Applying migration SQL...\n";

try {
    // Split SQL into individual statements (basic split by semicolon)
    // Note: This is a simple splitter and might fail with complex SQL (like functions)
    // For this migration, it should be fine.
    $statements = explode(';', $sql);

    foreach ($statements as $statement) {
        $statement = trim($statement);
        if (empty($statement))
            continue;

        echo "Executing: " . substr($statement, 0, 50) . "...\n";
        $db->query($statement);
    }

    echo "Migration SQL applied successfully!\n";
} catch (Exception $e) {
    echo "Error applying migration: " . $e->getMessage() . "\n";
    exit(1);
}
