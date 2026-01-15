<?php
/**
 * DB Import from Seed Script
 * Imports the current state SQL file into the database.
 */

require_once __DIR__ . '/../includes/database.php';

$inputFile = __DIR__ . '/../data/current-state-seed.sql';

if (!file_exists($inputFile)) {
    die("❌ Error: Seed file not found at $inputFile\n");
}

$db = new DatabaseClient();
$pdo = $db->getConnection();

echo "📥 Importing current state seed...\n";

try {
    $sql = file_get_contents($inputFile);

    // Split by semicolons followed by newline for basic multi-statement handling
    // or just execute the whole thing if PostgreSQL allows.
    // PostgreSQL exec() can handle multiple statements.
    $pdo->exec($sql);

    echo "✅ Import successful!\n";
} catch (Exception $e) {
    echo "❌ Import failed: " . $e->getMessage() . "\n";
    exit(1);
}
