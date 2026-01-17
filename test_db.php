<?php
require_once __DIR__ . '/includes/database.php';

try {
    $db = new DatabaseClient();
    $table = 'posts';
    $where = [
        'post_type' => 'blog',
        'select' => 'id, title, slug, post_type, post_status, updated_at, created_at, excerpt, featured_image, gallery_folder_id'
    ];

    $items = $db->select($table, $where);
    echo "Success! Found " . count($items) . " items.\n";
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
