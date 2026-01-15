<?php
require_once __DIR__ . '/includes/database.php';
$db = new DatabaseClient();
$posts = $db->select('posts', ['slug' => 'homepage']);
if (empty($posts)) {
    echo "Homepage post not found!\n";
} else {
    $post = $posts[0];
    echo "Title: {$post['title']}\n";
    echo "Slug: {$post['slug']}\n";
    echo "Content Length: " . strlen($post['content']) . "\n";
    echo "--- Content Start ---\n";
    echo $post['content'] . "\n";
    echo "--- Content End ---\n";
}
