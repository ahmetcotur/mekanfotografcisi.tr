<?php
require_once __DIR__ . '/api/middleware.php';
require_once __DIR__ . '/includes/database.php';

// addCorsHeaders();
// $user = requireAuth();

try {
    $db = new DatabaseClient();
    $result = $db->query("SELECT table_name FROM information_schema.tables WHERE table_schema = 'public'");
    echo json_encode(['success' => true, 'tables' => array_column($result, 'table_name')]);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
