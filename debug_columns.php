<?php
require_once __DIR__ . '/includes/database.php';
$db = new DatabaseClient();
$pdo = $db->getPdo();

try {
    $stmt = $pdo->query("SELECT * FROM seo_pages LIMIT 1");
    $rowCount = $stmt->columnCount();
    echo "Columns in 'seo_pages':\n";
    for ($i = 0; $i < $rowCount; $i++) {
        $meta = $stmt->getColumnMeta($i);
        echo "- " . $meta['name'] . "\n";
    }
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
