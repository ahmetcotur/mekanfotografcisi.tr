<?php
/**
 * Content Migration Script
 * Moves data from services, locations, and portfolio_projects to the new posts table.
 */

require_once __DIR__ . '/includes/database.php';

$db = new DatabaseClient();

echo "Starting migration...\n";

// Truncate new tables for a clean start
echo "Cleaning up new tables...\n";
$db->query("TRUNCATE posts CASCADE");
$db->query("TRUNCATE post_meta CASCADE");

// 1. Migrate Services
echo "Migrating Services...\n";
$services = $db->select('services');
foreach ($services as $service) {
    echo "Processing service: {$service['name']}\n";
    $db->insert('posts', [
        'id' => $service['id'],
        'title' => $service['name'],
        'slug' => $service['slug'],
        'content' => $service['short_intro'], // Currently short intro is all we have
        'post_type' => 'service',
        'post_status' => $service['is_active'] ? 'publish' : 'draft',
        'created_at' => $service['created_at'],
        'updated_at' => $service['updated_at']
    ]);
}

// 2. Migrate Portfolio Projects
echo "Migrating Portfolio Projects...\n";
$projects = $db->select('portfolio_projects');
foreach ($projects as $project) {
    echo "Processing project: {$project['title']}\n";
    $db->insert('posts', [
        'id' => $project['id'],
        'title' => $project['title'],
        'slug' => $project['slug'],
        'content' => $project['description'],
        'post_type' => 'portfolio',
        'post_status' => $project['is_published'] ? 'publish' : 'draft',
        'created_at' => $project['created_at'],
        'updated_at' => $project['updated_at']
    ]);

    // Migrate Project Meta (District, Province)
    if (!empty($project['province_id'])) {
        $db->insert('post_meta', [
            'post_id' => $project['id'],
            'meta_key' => 'province_id',
            'meta_value' => json_encode($project['province_id'])
        ]);
    }
}

// 3. Migrate SEO Pages (merged with posts now)
echo "Migrating SEO Pages...\n";
$seo_pages = $db->select('seo_pages');
foreach ($seo_pages as $page) {
    echo "Processing SEO page: {$page['title']}\n";

    // Use existing SEO page data to enrich the posts
    // If a post with same slug already exists (from services/portfolio), we should update it
    // But since seo_pages have unique slugs like /services/slug-location, they are usually distinct.

    $cleanSlug = trim($page['slug'], '/');
    $existing = $db->select('posts', ['slug' => $cleanSlug]);

    $data = [
        'title' => $page['title'],
        'content' => $page['content_md'],
        'post_type' => 'seo_page',
        'post_status' => $page['published'] ? 'publish' : 'draft'
    ];

    if (empty($existing)) {
        $db->insert('posts', array_merge($data, [
            'id' => $page['id'],
            'slug' => $cleanSlug,
            'created_at' => $page['created_at'],
            'updated_at' => $page['updated_at']
        ]));
    } else {
        $db->update('posts', $data, ['slug' => $cleanSlug]);
    }

    // Add SEO meta
    $meta = [
        'meta_description' => $page['meta_description'],
        'h1' => $page['h1'],
        'faq_json' => json_encode($page['faq_json'])
    ];

    foreach ($meta as $key => $val) {
        $db->insert('post_meta', [
            'post_id' => $page['id'],
            'meta_key' => $key,
            'meta_value' => json_encode($val)
        ]);
    }
}

echo "Migration completed!\n";
