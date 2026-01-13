<?php
/**
 * Local to Production Database Sync Tool
 * Run this LOCALLY on your Mac to push your local settings/homepage to the live server.
 * Usage: php scripts/sync-to-prod.php
 */

require_once __DIR__ . '/../includes/database.php';
require_once __DIR__ . '/../includes/helpers.php';

function log_msg($msg)
{
    if (php_sapi_name() === 'cli') {
        echo $msg . "\n";
    } else {
        echo $msg . "<br>";
    }
}

// 1. Production Credentials
$prod_config = [
    'host' => 'rswgogs4kg8cc4kcscwo48cs',
    'port' => '5432',
    'dbname' => 'postgres',
    'user' => 'postgres',
    'pass' => 'VzwQ8NcJfiv5nBfHejbr4UQ4zNbomLM9MuX65JhR3DKGAXHWSVoGQXSg5mM3ohzW'
];

try {
    // 2. Connect to Local DB
    log_msg("🔌 Connecting to LOCAL database...");
    $local_db = new DatabaseClient();

    // 3. Connect to Production DB
    log_msg("🔌 Connecting to PRODUCTION database...");
    $dsn = "pgsql:host={$prod_config['host']};port={$prod_config['port']};dbname={$prod_config['dbname']}";
    $prod_pdo = new PDO($dsn, $prod_config['user'], $prod_config['pass'], [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
    ]);
    log_msg("✅ Connected to both databases.");

    // 4. Tables to Sync
    $tables = ['settings', 'posts', 'post_meta'];

    foreach ($tables as $table) {
        log_msg("📊 Syncing table: {$table}...");

        // Fetch local data
        $local_data = $local_db->select($table);
        log_msg("   - Found " . count($local_data) . " rows locally.");

        if (empty($local_data))
            continue;

        // Clear production table (caution!)
        // In a real scenario, you might want to UPSERT instead of DELETE
        // But for "matching exactly", clearing and re-inserting is simplest for now
        $prod_pdo->exec("TRUNCATE TABLE {$table} CASCADE");
        log_msg("   - Production table cleared.");

        // Insert rows into production
        foreach ($local_data as $row) {
            $keys = array_keys($row);
            $values = array_values($row);
            $placeholders = array_fill(0, count($keys), '?');

            $sql = "INSERT INTO {$table} (" . implode(', ', array_map(fn($k) => "\"$k\"", $keys)) . ") VALUES (" . implode(', ', $placeholders) . ")";

            // Handle JSON/Arrays for PDO
            foreach ($values as &$v) {
                if (is_array($v))
                    $v = json_encode($v);
            }

            $stmt = $prod_pdo->prepare($sql);
            $stmt->execute($values);
        }
        log_msg("   ✅ Table {$table} synced successfully.");
    }

    log_msg("\n✨ Database synchronization completed! Your live site now matches your local environment.");
    log_msg("📢 IMPORTANT: Make sure you have also pushed your 'uploads/' folder changes to Git if you added a new logo file.");

} catch (Exception $e) {
    log_msg("❌ Error: " . $e->getMessage());
    exit(1);
}
