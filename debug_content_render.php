<?php
require_once __DIR__ . '/includes/database.php';
require_once __DIR__ . '/includes/Core/Post.php';

$db = new DatabaseClient();
$post = Core\Post::findBySlug('homepage', $db);

if (!$post) {
    echo "Homepage post NOT FOUND.\n";
    exit;
}

echo "Post ID: " . $post->id . "\n";
echo "Content Length: " . strlen($post->content) . "\n";
echo "First 500 chars:\n";
echo substr($post->content, 0, 500) . "\n";
