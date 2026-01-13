<?php
require_once __DIR__ . '/includes/database.php';
$db = new DatabaseClient();
$posts = $db->query("SELECT title, slug, post_type, post_status FROM posts LIMIT 20");
echo "Dumping first 20 posts:\n";
foreach ($posts as $post) {
    echo "Title: {$post['title']} | Slug: [{$post['slug']}] | Type: {$post['post_type']} | Status: {$post['post_status']}\n";
}
