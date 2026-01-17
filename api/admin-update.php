<?php
// API Endpoint for Admin Updates (Locations & Services)
// Handles AJAX requests from admin views
require_once __DIR__ . '/middleware.php';
require_once __DIR__ . '/../includes/database.php';

addCorsHeaders();
file_put_contents('/tmp/api_debug.log', date('[Y-m-d H:i:s] ') . "API Request Start: " . $_SERVER['REQUEST_URI'] . "\n", FILE_APPEND);
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
        $allowed_tables = ['locations_province', 'locations_district', 'locations_town', 'locations_city_distance', 'services', 'posts', 'quotes', 'media', 'media_folders', 'seo_pages', 'settings', 'freelancer_applications'];

        if (!in_array($table, $allowed_tables)) {
            throw new Exception("Invalid table specified for list: $table");
        }

        $where = [];
        if ($table === 'posts' && isset($data['post_type'])) {
            $where['post_type'] = $data['post_type'];
        }
        if ($table === 'posts' && isset($data['post_status'])) {
            $where['post_status'] = $data['post_status'];
        }
        if ($table === 'posts' && isset($data['slug'])) {
            $where['slug'] = $data['slug'];
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

        // Optimize: for posts list, don't fetch full content by default
        if ($table === 'posts' && !isset($data['select'])) {
            $where['select'] = 'id, title, slug, post_type, post_status, updated_at, created_at, excerpt, featured_image, gallery_folder_id';
        } elseif (isset($data['select'])) {
            $where['select'] = $data['select'];
        }

        file_put_contents('/tmp/api_debug.log', date('[Y-m-d H:i:s] ') . "About to query table=$table with where=" . json_encode($where) . "\n", FILE_APPEND);
        $items = $db->select($table, $where);
        file_put_contents('/tmp/api_debug.log', date('[Y-m-d H:i:s] ') . "Query successful, got " . count($items) . " items\n", FILE_APPEND);
        echo json_encode(['success' => true, 'data' => $items ?: []]);
        exit;

    } elseif ($action === 'get') {
        $table = $data['table'] ?? '';
        $id = $data['id'] ?? '';

        $allowed_tables = ['locations_province', 'locations_district', 'locations_town', 'locations_city_distance', 'services', 'posts', 'quotes', 'media', 'media_folders', 'settings', 'freelancer_applications'];
        if (!in_array($table, $allowed_tables)) {
            throw new Exception("Invalid table specified for get: $table");
        }

        $where = ($table === 'settings') ? ['key' => $id] : ['id' => $id];
        $item = $db->select($table, $where);
        if (empty($item)) {
            throw new Exception('Item not found');
        }

        echo json_encode(['success' => true, 'data' => $item[0]]);
        exit;

    } elseif ($action === 'update') {
        $table = $data['table'] ?? '';
        $id = $data['id'] ?? '';
        $ids = $data['ids'] ?? [];
        $updateData = $data['data'] ?? [];

        if (empty($table) || (empty($id) && empty($ids)) || empty($updateData)) {
            throw new Exception('Missing required parameters for update');
        }

        // Allowed tables check
        $allowed_tables = ['locations_province', 'locations_district', 'locations_town', 'locations_city_distance', 'services', 'posts', 'quotes', 'settings', 'seo_pages', 'freelancer_applications'];
        if (!in_array($table, $allowed_tables)) {
            throw new Exception("Invalid table specified for update: $table");
        }

        $targetIds = !empty($ids) ? $ids : [$id];

        // If updating 'is_active', handle boolean correctly for Postgres
        if (isset($updateData['is_active'])) {
            $isActive = filter_var($updateData['is_active'], FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);
            if ($isActive !== null) {
                $updateData['is_active'] = $isActive;
            } else {
                unset($updateData['is_active']);
            }
        }

        foreach ($targetIds as $targetId) {
            $where = ($table === 'settings') ? ['key' => $targetId] : ['id' => $targetId];
            $db->update($table, $updateData, $where);

            // Cascading deactivation for locations
            if (isset($updateData['is_active']) && $updateData['is_active'] === false) {
                if ($table === 'locations_province') {
                    $db->update('locations_district', ['is_active' => false], ['province_id' => $targetId]);
                } elseif ($table === 'locations_district') {
                    $db->update('locations_town', ['is_active' => false], ['district_id' => $targetId]);
                }
            }
        }

        echo json_encode(['success' => true]);
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
            'featured_image' => $data['featured_image'] ?? null,
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
        $ids = $data['ids'] ?? [];

        if (empty($table) || (empty($id) && empty($ids))) {
            throw new Exception('Missing required parameters for delete');
        }

        $allowed_tables = ['locations_province', 'locations_district', 'locations_town', 'locations_city_distance', 'services', 'posts', 'quotes', 'media', 'media_folders', 'seo_pages', 'freelancer_applications'];
        if (!in_array($table, $allowed_tables)) {
            throw new Exception("Invalid table specified for delete: $table");
        }

        $targetIds = !empty($ids) ? $ids : [$id];

        foreach ($targetIds as $targetId) {
            // Cascading Delete
            if ($table === 'locations_province') {
                $db->delete('locations_district', ['province_id' => $targetId]);
            }
            if ($table === 'locations_district') {
                $db->delete('locations_town', ['district_id' => $targetId]);
            }
            $db->delete($table, ['id' => $targetId]);
        }

        echo json_encode(['success' => true, 'deleted' => true]);
        exit;

    } elseif ($action === 'save-location') {
        $table = $data['table'] ?? ''; // locations_province or locations_district
        $id = $data['id'] ?? null;
        $name = $data['name'] ?? '';
        $slug = $data['slug'] ?? '';
        $is_active = filter_var($data['is_active'] ?? ($table === 'locations_town' ? false : true), FILTER_VALIDATE_BOOLEAN);

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

    } elseif ($action === 'get-available-towns') {
        $province = $data['province'] ?? '';
        $district = $data['district'] ?? '';

        require_once __DIR__ . '/../includes/Core/TurkeyLocationService.php';
        $locationService = new \Core\TurkeyLocationService();
        $towns = $locationService->getTowns($province, $district);

        echo json_encode(['success' => true, 'data' => $towns]);
        exit;

    } elseif ($action === 'import-locations') {
        $scriptPath = __DIR__ . '/../import_locations.php';
        // Run in background
        exec("php $scriptPath > /dev/null 2>&1 &");
        echo json_encode(['success' => true, 'message' => 'Import started in background']);
        exit;

    } elseif ($action === 'get-distances') {
        $provinceId = $data['province_id'] ?? null;
        if (!$provinceId)
            throw new Exception('Province ID required');

        $distances = $db->query("
            SELECT d.*, p2.name as to_province_name 
            FROM locations_city_distance d
            JOIN locations_province p2 ON d.province_to_id = p2.id
            WHERE d.province_from_id = :id
            ORDER BY p2.name ASC
        ", ['id' => $provinceId]);

        echo json_encode(['success' => true, 'data' => $distances]);
        exit;
    }

} catch (Exception $e) {
    http_response_code(400);
    error_log("API Error: " . $e->getMessage() . " in " . $e->getFile() . ":" . $e->getLine());
    file_put_contents('/tmp/api_debug.log', date('[Y-m-d H:i:s] ') . "EXCEPTION: " . $e->getMessage() . " in " . $e->getFile() . ":" . $e->getLine() . "\n", FILE_APPEND);
    file_put_contents(__DIR__ . '/../debug_log.txt', date('[Y-m-d H:i:s] ') . "API Error: " . $e->getMessage() . "\n" . print_r($data, true) . "\n", FILE_APPEND);
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}