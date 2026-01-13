<?php
require_once __DIR__ . '/includes/database.php';

$db = new DatabaseClient();
$pages = $db->select('seo_pages');

echo "Count: " . count($pages) . "\n";
print_r($pages);
