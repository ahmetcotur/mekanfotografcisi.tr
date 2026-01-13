<?php
require_once __DIR__ . '/includes/database.php';
$db = new DatabaseClient();

$slug = 'locations/antalya/kas';
$post = $db->select('posts', ['slug' => $slug]);

if ($post) {
    echo "Post Type: " . $post[0]['post_type'] . "\n";
} else {
    echo "Post Not Found\n";
}
