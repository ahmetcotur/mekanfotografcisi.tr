<?php
require_once __DIR__ . '/includes/config.php';

echo json_encode([
    'DB_HOST' => env('DB_HOST', 'not set'),
    'DB_NAME' => env('DB_NAME', 'not set'),
    'DB_USER' => env('DB_USER', 'not set'),
    'DB_PORT' => env('DB_PORT', 'not set'),
    'HAS_PASSWORD' => !empty(env('DB_PASSWORD')),
    'PHP_VERSION' => PHP_VERSION,
    'SERVER_SOFTWARE' => $_SERVER['SERVER_SOFTWARE'] ?? 'unknown'
]);

try {
    require_once __DIR__ . '/includes/database.php';
    $db = new DatabaseClient();
    $counts = [
        'provinces' => count($db->select('locations_province', [])),
        'districts' => count($db->select('locations_district', [])),
        'posts' => count($db->select('posts', [])),
        'quotes' => count($db->select('quotes', []))
    ];
    echo json_encode(['success' => true, 'counts' => $counts]);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'db_error' => $e->getMessage()]);
}
