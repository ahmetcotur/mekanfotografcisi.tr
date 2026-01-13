<?php
require_once __DIR__ . '/includes/database.php';
$db = new DatabaseClient();

// Mimic the query in services-grid.php
$services = $db->select('posts', ['post_type' => 'service', 'post_status' => 'publish', 'limit' => 6]);

echo "Services Found: " . count($services) . "\n";
foreach ($services as $s) {
    echo "- " . $s['title'] . " (" . $s['post_status'] . ")\n";
}
