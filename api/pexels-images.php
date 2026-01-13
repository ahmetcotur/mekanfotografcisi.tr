<?php
/**
 * Pexels Images Management API
 */
// Session is now started globally in router.php
require_once __DIR__ . '/middleware.php';
require_once __DIR__ . '/../includes/database.php';

addCorsHeaders();
$user = requireAuth();

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
        } elseif ($action === 'sync') {
            try {
                if (!file_exists(__DIR__ . '/../includes/Core/PexelsService.php')) {
                    throw new Exception("Service file not found at " . __DIR__ . '/../includes/Core/PexelsService.php');
                }
                require_once __DIR__ . '/../includes/Core/PexelsService.php';

                if (!class_exists('\\Core\\PexelsService')) {
                    throw new Exception("Class \\Core\\PexelsService not found");
                }

                $service = new \Core\PexelsService();
                $photos = $service->getPhotos();
                $count = 0;

                foreach ($photos as $photo) {
                    // Check exist
                    $exists = $db->query("SELECT id FROM pexels_images WHERE image_url = ?", [$photo['src']]);
                    if (!$exists) {
                        $db->insert('pexels_images', [
                            'image_url' => $photo['src'],
                            'photographer' => $photo['photographer'],
                            'is_visible' => true,
                            'display_order' => 1000 + $count // Append to end
                        ]);
                        $count++;
                    }
                }

                echo json_encode(['success' => true, 'synced_count' => $count]);
            } catch (\Throwable $t) {
                http_response_code(500);
                echo json_encode(['success' => false, 'error' => $t->getMessage(), 'file' => $t->getFile(), 'line' => $t->getLine()]);
            }

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
