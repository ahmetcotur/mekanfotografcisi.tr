<?php
// debug_slug.php
require_once __DIR__ . '/includes/database.php';
require_once __DIR__ . '/includes/helpers.php';
require_once __DIR__ . '/includes/Core/Post.php';

use Core\Post;

$db = new DatabaseClient();
$slug = $_GET['slug'] ?? 'locations/adana';

echo "<pre>";
echo "Checking slug: $slug\n";

$post = Post::findBySlug($slug, $db);

if (!$post) {
    echo "Post Not Found via findBySlug\n";
    // Check manual DB
    $raw = $db->select('posts', ['slug' => $slug]);
    echo "Raw DB check for slug: " . print_r($raw, true) . "\n";
} else {
    echo "Post Found:\n";
    print_r($post);

    echo "\nMethods test:\n";
    echo "Post Type: " . $post->post_type . "\n";

    $pid = $post->getMeta('province_id');
    echo "Meta 'province_id': " . var_export($pid, true) . "\n";

    if ($pid) {
        $prov = $db->select('locations_province', ['id' => $pid]);
        echo "Province Record:\n";
        print_r($prov);

        if (!empty($prov)) {
            $isActive = $prov[0]['is_active'];
            echo "is_active value: " . var_export($isActive, true) . "\n";
            echo "Type of is_active: " . gettype($isActive) . "\n";

            echo "Check: (isActive === false): " . ($isActive === false ? 'YES' : 'NO') . "\n";
            echo "Check: (isActive === 'false'): " . ($isActive === 'false' ? 'YES' : 'NO') . "\n";
            echo "Check: (isActive == false): " . ($isActive == false ? 'YES' : 'NO') . "\n";
        }
    } else {
        echo "No province_id meta found. Dumping all meta:\n";
        print_r($post->getMeta());
    }
}
echo "</pre>";
