<?php
/**
 * DB Export to Seed Script
 * Exports current database state to a SQL file for versioning and seeding.
 */

require_once __DIR__ . '/../includes/database.php';

$outputFile = __DIR__ . '/../data/current-state-seed.sql';
$db = new DatabaseClient();
$pdo = $db->getConnection();

$tables = [
    'settings',
    'posts',
    'post_meta',
    'locations_province',
    'locations_district',
    'locations_town',
    'locations_city_distance'
];

$sqlOutput = "-- Mekan Fotografcisi DB Dump\n";
$sqlOutput .= "-- Generated at: " . date('Y-m-d H:i:s') . "\n\n";

// Disable foreign key checks for the session if needed (PostgreSQL uses TRUNCATE CASCADE usually)
$sqlOutput .= "SET statement_timeout = 0;\n";
$sqlOutput .= "SET lock_timeout = 0;\n";
$sqlOutput .= "SET client_encoding = 'UTF8';\n";
$sqlOutput .= "SET standard_conforming_strings = on;\n";
$sqlOutput .= "SET check_function_bodies = false;\n";
$sqlOutput .= "SET xmloption = content;\n";
$sqlOutput .= "SET client_min_messages = warning;\n";
$sqlOutput .= "SET row_security = off;\n\n";

foreach ($tables as $table) {
    echo "Exporting $table...\n";
    $sqlOutput .= "-- Data for table: $table\n";
    $sqlOutput .= "TRUNCATE TABLE \"$table\" CASCADE;\n\n";

    $rows = $db->select($table);
    if (empty($rows)) {
        $sqlOutput .= "-- No data for $table\n\n";
        continue;
    }

    foreach ($rows as $row) {
        $columns = array_keys($row);
        $values = [];

        foreach ($row as $col => $val) {
            if ($val === null) {
                $values[] = "NULL";
            } elseif (is_bool($val)) {
                $values[] = $val ? "true" : "false";
            } elseif ($table === 'settings' && $col === 'value' && str_starts_with($val, 'sk-')) {
                // Mask OpenAI Keys
                $values[] = $pdo->quote('[MASKED_OPENAI_KEY]');
            } elseif (is_numeric($val) && !is_string($val)) {
                $values[] = $val;
            } else {
                // Handle JSON and special characters
                if (is_array($val))
                    $val = json_encode($val);
                $values[] = $pdo->quote($val);
            }
        }

        $sqlOutput .= "INSERT INTO \"$table\" (" . implode(', ', array_map(fn($c) => "\"$c\"", $columns)) . ") VALUES (" . implode(', ', $values) . ");\n";
    }
    $sqlOutput .= "\n";
}

file_put_contents($outputFile, $sqlOutput);
echo "✅ Export completed! SQL file saved to: $outputFile\n";
