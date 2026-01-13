<?php
/**
 * Dashboard Stats API
 * Returns statistics for admin dashboard
 */
require_once __DIR__ . '/middleware.php';
require_once __DIR__ . '/../includes/database.php';

addCorsHeaders();
$user = requireAuth();

$db = new DatabaseClient();

try {
    // Get stats
    $stats = [
        'services' => count($db->select('posts', ['post_type' => 'service', 'post_status' => 'publish'])),
        'seo_pages' => count($db->select('posts', ['post_type' => 'seo_page', 'post_status' => 'publish'])),
        'provinces' => count($db->select('locations_province', ['is_active' => true])),
        'districts' => count($db->select('locations_district', ['is_active' => true])),
        'total_quotes' => count($db->query("SELECT id FROM quotes")),
        'new_quotes' => count($db->query("SELECT id FROM quotes WHERE is_read = false"))
    ];

    // Recent quotes
    $recentQuotes = $db->query("SELECT * FROM quotes ORDER BY created_at DESC LIMIT 5");

    // Recent pages
    $recentPages = $db->query("SELECT * FROM posts WHERE post_type IN ('service', 'seo_page', 'page') ORDER BY created_at DESC LIMIT 5");

    jsonSuccess([
        'stats' => $stats,
        'recent_quotes' => $recentQuotes ?: [],
        'recent_pages' => $recentPages ?: []
    ]);
} catch (Exception $e) {
    jsonError('Failed to fetch dashboard stats: ' . $e->getMessage(), 500);
}
