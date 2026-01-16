#!/usr/bin/env php
<?php
/**
 * Migration Runner
 * Runs all pending database migrations
 */

require_once __DIR__ . '/includes/helpers.php';
require_once __DIR__ . '/includes/database.php';

echo "🗄️  Running Database Migrations\n";
echo "================================\n\n";

$db = new DatabaseClient();

// List of migrations to run
$migrations = [
    'scripts/migrations/20260114_006_create_freelancer_applications_table.sql',
    'scripts/migrations/20260116_001_create_freelancer_assignment_system.sql'
];

$success = 0;
$failed = 0;

foreach ($migrations as $migration) {
    $file = __DIR__ . '/' . $migration;

    if (!file_exists($file)) {
        echo "❌ Migration file not found: $migration\n";
        $failed++;
        continue;
    }

    echo "📝 Running: $migration\n";

    try {
        $sql = file_get_contents($file);

        // Execute the SQL
        $db->getConnection()->exec($sql);

        echo "✅ Success: $migration\n\n";
        $success++;
    } catch (Exception $e) {
        echo "❌ Failed: $migration\n";
        echo "   Error: " . $e->getMessage() . "\n\n";
        $failed++;
    }
}

echo "\n================================\n";
echo "✅ Successful: $success\n";
echo "❌ Failed: $failed\n";
echo "================================\n";

if ($failed > 0) {
    exit(1);
}

exit(0);
