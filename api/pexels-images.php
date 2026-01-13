<?php
/**
 * Pexels Images Management API
 */
// Session is now started globally in router.php
require_once __DIR__ . '/../includes/database.php';

header('Content-Type: application/json');

// Check if user is authenticated
if (!isset($_SESSION['admin_user_id'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'error' => 'Unauthorized']);
    exit;
}

$db = new DatabaseClient();

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    // Get all Pexels images
    try {
        $images = $db->query("SELECT * FROM pexels_images ORDER BY display_order ASC, created_at DESC");
        echo json_encode(['success' => true, 'images' => $images ?: []]);
    } catch (Exception $e) {
        // Table might not exist yet
        echo json_encode(['success' => true, 'images' => []]);
    }
} elseif ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $input = json_decode(file_get_contents('php://input'), true);
    $action = $input['action'] ?? '';

    try {
        if ($action === 'toggle') {
            $id = $input['id'] ?? 0;
            $isVisible = filter_var($input['is_visible'], FILTER_VALIDATE_BOOLEAN);

            $db->update('pexels_images', [
                'is_visible' => $isVisible,
                'updated_at' => date('Y-m-d H:i:s')
            ], ['id' => $id]);

            echo json_encode(['success' => true]);
        } elseif ($action === 'delete') {
            $id = $input['id'] ?? 0;
            $db->delete('pexels_images', ['id' => $id]);
            echo json_encode(['success' => true]);
        } elseif ($action === 'add') {
            $imageUrl = $input['image_url'] ?? '';
            $photographer = $input['photographer'] ?? '';

            if (empty($imageUrl)) {
                throw new Exception('Image URL is required');
            }

            // Get max display_order
            $maxOrder = $db->query("SELECT MAX(display_order) as max_order FROM pexels_images");
            $nextOrder = ($maxOrder && isset($maxOrder[0]['max_order'])) ? $maxOrder[0]['max_order'] + 1 : 1;

            $db->insert('pexels_images', [
                'image_url' => $imageUrl,
                'photographer' => $photographer,
                'is_visible' => true,
                'display_order' => $nextOrder
            ]);

            echo json_encode(['success' => true]);
        } else {
            throw new Exception('Invalid action');
        }
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(['success' => false, 'error' => $e->getMessage()]);
    }
} else {
    http_response_code(405);
    echo json_encode(['success' => false, 'error' => 'Method not allowed']);
}
