<?php
// API Endpoint for Admin Updates (Locations & Services)
// Handles AJAX requests from admin views
require_once __DIR__ . '/middleware.php';
require_once __DIR__ . '/../includes/database.php';

addCorsHeaders();
$user = requireAuth();

try {
    // Get data from either JSON payload, POST, or GET
    $input = file_get_contents('php://input');
    $jsonData = json_decode($input, true);

    if ($jsonData) {
        $data = $jsonData;
    } elseif (!empty($_POST)) {
        $data = $_POST;
    } else {
        $data = $_GET;
    }

    $action = $data['action'] ?? 'update';

    // Debug log
    // error_log("API Request: action=$action, data=" . json_encode($data));

    $db = new DatabaseClient();

    if ($action === 'list') {
        $table = $data['table'] ?? '';
        $allowed_tables = ['locations_province', 'locations_district', 'locations_town', 'services', 'posts', 'quotes', 'media', 'media_folders', 'seo_pages'];

        if (!in_array($table, $allowed_tables)) {
            throw new Exception("Invalid table specified for list: $table");
        }

        $where = [];
        if ($table === 'posts' && isset($data['post_type'])) {
            $where['post_type'] = $data['post_type'];
        }
        if ($table === 'locations_district' && isset($data['province_id'])) {
            $where['province_id'] = $data['province_id'];
        }
        if ($table === 'locations_town' && isset($data['district_id'])) {
            $where['district_id'] = $data['district_id'];
        }
        if ($table === 'media' && isset($data['folder_id'])) {
            $where['folder_id'] = $data['folder_id'];
        }

        $items = $db->select($table, $where);
        echo json_encode(['success' => true, 'data' => $items ?: []]);
        exit;

    } elseif ($action === 'get') {
        $table = $data['table'] ?? '';
        $id = $data['id'] ?? '';

        $allowed_tables = ['locations_province', 'locations_district', 'locations_town', 'services', 'posts', 'quotes', 'media', 'media_folders', 'seo_pages'];
        if (!in_array($table, $allowed_tables)) {
            throw new Exception("Invalid table specified for get: $table");
        }

        $item = $db->select($table, ['id' => $id]);
        if (empty($item)) {
            throw new Exception('Item not found');
        }

        echo json_encode(['success' => true, 'data' => $item[0]]);
        exit;

    } elseif ($action === 'update') {
        $table = $data['table'] ?? '';
        $id = $data['id'] ?? '';
        $updateData = $data['data'] ?? [];

        if (empty($table) || empty($id) || empty($updateData)) {
            throw new Exception('Missing required parameters for update');
        }

        // Allowed tables check
        $allowed_tables = ['locations_province', 'locations_district', 'locations_town', 'services', 'posts', 'quotes', 'settings', 'seo_pages'];
        if (!in_array($table, $allowed_tables)) {
            throw new Exception("Invalid table specified for update: $table");
        }

        // If updating 'is_active', handle boolean correctly for Postgres
        if (isset($updateData['is_active'])) {
            if ($updateData['is_active'] === '' || $updateData['is_active'] === null) {
                unset($updateData['is_active']);
            } else {
                $isActive = filter_var($updateData['is_active'], FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);
                if ($isActive === null) {
                    unset($updateData['is_active']);
                } else {
                    $updateData['is_active'] = $isActive;

                    if ($table === 'locations_province' && $isActive === false) {
                        $db->update('locations_district', ['is_active' => false], ['province_id' => $id]);
                    }
                }
            }
        }

        $result = $db->update($table, $updateData, ['id' => $id]);
        echo json_encode(['success' => true, 'data' => $result]);
        exit;

    } elseif ($action === 'save-post') {
        $id = $data['id'] ?? null;
        $title = $data['title'] ?? '';
        $slug = $data['slug'] ?? '';
        $content = $data['content'] ?? '';
        $post_type = $data['post_type'] ?? 'page';
        $post_status = $data['post_status'] ?? 'draft';

        if (empty($title)) {
            throw new Exception('Title is required');
        }

        if (empty($slug)) {
            require_once __DIR__ . '/../includes/helpers.php';
            $slug = to_permalink($title);
        }

        $dbData = [
            'title' => $title,
            'slug' => $slug,
            'content' => $content,
            'excerpt' => $data['excerpt'] ?? '',
            'post_type' => $post_type,
            'post_status' => $post_status,
            'gallery_folder_id' => !empty($data['gallery_folder_id']) ? $data['gallery_folder_id'] : null,
            'updated_at' => date('Y-m-d H:i:s')
        ];

        if ($id) {
            $result = $db->update('posts', $dbData, ['id' => $id]);
            $resultId = $id;
        } else {
            // Generate UUID if not provided by DB automatically (for safety)
            $dbData['id'] = sprintf(
                '%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
                mt_rand(0, 0xffff),
                mt_rand(0, 0xffff),
                mt_rand(0, 0xffff),
                mt_rand(0, 0x0fff) | 0x4000,
                mt_rand(0, 0x3fff) | 0x8000,
                mt_rand(0, 0xffff),
                mt_rand(0, 0xffff),
                mt_rand(0, 0xffff)
            );
            $dbData['created_at'] = date('Y-m-d H:i:s');
            $result = $db->insert('posts', $dbData);
            $resultId = $dbData['id'];
        }

        echo json_encode(['success' => true, 'id' => $resultId, 'data' => $result]);
        exit;

    } elseif ($action === 'delete') {
        $table = $data['table'] ?? '';
        $id = $data['id'] ?? '';

        if (empty($table) || empty($id)) {
            throw new Exception('Missing required parameters for delete');
        }

        $allowed_tables = ['locations_province', 'locations_district', 'locations_town', 'services', 'posts', 'quotes', 'media', 'media_folders', 'seo_pages'];
        if (!in_array($table, $allowed_tables)) {
            throw new Exception("Invalid table specified for delete: $table");
        }

        // Cascading Delete
        if ($table === 'locations_province') {
            $db->delete('locations_district', ['province_id' => $id]);
        }
        if ($table === 'locations_district') {
            $db->delete('locations_town', ['district_id' => $id]);
        }

        $result = $db->delete($table, ['id' => $id]);
        echo json_encode(['success' => true, 'deleted' => true]);
        exit;

    } elseif ($action === 'save-location') {
        $table = $data['table'] ?? ''; // locations_province or locations_district
        $id = $data['id'] ?? null;
        $name = $data['name'] ?? '';
        $slug = $data['slug'] ?? '';
        $is_active = filter_var($data['is_active'] ?? true, FILTER_VALIDATE_BOOLEAN);

        if (empty($name))
            throw new Exception('Name is required');
        if (empty($slug)) {
            require_once __DIR__ . '/../includes/helpers.php';
            $slug = to_permalink($name);
        }

        $dbData = [
            'name' => $name,
            'slug' => $slug,
            'is_active' => $is_active,
            'updated_at' => date('Y-m-d H:i:s')
        ];

        if ($table === 'locations_district') {
            $dbData['province_id'] = $data['province_id'];
        }

        if ($table === 'locations_town') {
            $dbData['district_id'] = $data['district_id'];
        }

        if ($id) {
            $result = $db->update($table, $dbData, ['id' => $id]);
        } else {
            $dbData['id'] = sprintf(
                '%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
                mt_rand(0, 0xffff),
                mt_rand(0, 0xffff),
                mt_rand(0, 0xffff),
                mt_rand(0, 0x0fff) | 0x4000,
                mt_rand(0, 0x3fff) | 0x8000,
                mt_rand(0, 0xffff),
                mt_rand(0, 0xffff),
                mt_rand(0, 0xffff)
            );
            $dbData['created_at'] = date('Y-m-d H:i:s');
            $result = $db->insert($table, $dbData);
        }
        echo json_encode(['success' => true, 'data' => $result]);
        exit;

    }

    // Include previous specialized actions for compatibility
    require_once __DIR__ . '/admin-update-specialized.php';

} catch (Exception $e) {
    http_response_code(400);
    error_log("API Error: " . $e->getMessage() . " in " . $e->getFile() . ":" . $e->getLine());
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}