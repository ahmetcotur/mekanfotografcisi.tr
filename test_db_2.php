<?php
require_once __DIR__ . '/includes/database.php';

$db = new DatabaseClient();
$where = [
    'post_type' => 'blog',
    'select' => 'id, title, slug, post_type, post_status, updated_at, created_at, excerpt, featured_image, gallery_folder_id'
];

try {
    $items = $db->select('posts', $where);
    echo "SUCCESS: " . count($items) . " items\n";
} catch (Exception $e) {
    echo "FAILURE: " . $e->getMessage() . "\n";
}
