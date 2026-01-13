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

    // Debug log to see what's happening
    error_log("API Request: action=$action, data=" . json_encode($data));

    $db = new DatabaseClient();

    if ($action === 'list') {
        $table = $data['table'] ?? '';
        $allowed_tables = ['locations_province', 'locations_district', 'locations_town', 'services', 'posts', 'quotes'];

        if (!in_array($table, $allowed_tables)) {
            throw new Exception('Invalid table specified for list');
        }

        $where = [];
        if ($table === 'posts' && isset($data['post_type'])) {
            $where['post_type'] = $data['post_type'];
        }

        $items = $db->select($table, $where);
        echo json_encode(['success' => true, 'data' => $items ?: []]);
        exit;

    } elseif ($action === 'update') {
        $table = $data['table'] ?? '';
        $id = $data['id'] ?? '';
        $updateData = $data['data'] ?? [];

        if (empty($table) || empty($id) || empty($updateData)) {
            throw new Exception('Missing required parameters for update');
        }

        // Allowed tables check
        $allowed_tables = ['locations_province', 'locations_district', 'locations_town', 'services', 'posts'];
        if (!in_array($table, $allowed_tables)) {
            throw new Exception('Invalid table specified');
        }

        // If updating 'is_active', handle boolean correctly for Postgres
        if (isset($updateData['is_active'])) {
            // Skip if empty string (invalid boolean)
            if ($updateData['is_active'] === '' || $updateData['is_active'] === null) {
                unset($updateData['is_active']);
            } else {
                $isActive = filter_var($updateData['is_active'], FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);
                if ($isActive === null) {
                    unset($updateData['is_active']); // Invalid boolean value
                } else {
                    $updateData['is_active'] = $isActive;

                    // Cascading Update for Province
                    if ($table === 'locations_province' && $isActive === false) {
                        // Deactivate all districts
                        $db->update('locations_district', ['is_active' => false], ['province_id' => $id]);
                    }
                }
            }
        }

        $result = $db->update($table, $updateData, ['id' => $id]);

        if ($result) {
            echo json_encode(['success' => true, 'data' => $result]);
        } else {
            throw new Exception('Update failed or no changes made');
        }

    } elseif ($action === 'bulk-update') {
        $table = $data['table'] ?? '';
        $ids = $data['ids'] ?? [];
        $updateData = $data['data'] ?? [];

        if (empty($table) || empty($ids) || empty($updateData)) {
            throw new Exception('Missing required parameters for bulk update');
        }

        if (isset($updateData['is_active'])) {
            $updateData['is_active'] = filter_var($updateData['is_active'], FILTER_VALIDATE_BOOLEAN);
        }

        $results = [];

        foreach ($ids as $id) {
            // Cascading Logic
            if ($table === 'locations_province' && isset($updateData['is_active']) && $updateData['is_active'] === false) {
                $db->update('locations_district', ['is_active' => false], ['province_id' => $id]);
            }

            if ($db->update($table, $updateData, ['id' => $id])) {
                $results[] = $id;
            }
        }

        echo json_encode(['success' => true, 'updated_count' => count($results)]);

    } elseif ($action === 'get-missing-districts') {
        $province_id = $data['province_id'] ?? '';

        // 1. Get Province Name
        $prov = $db->select('locations_province', ['id' => $province_id]);
        if (empty($prov)) {
            echo json_encode(['success' => false, 'error' => 'Province not found']);
            exit;
        }
        $provName = $prov[0]['name'];

        // 2. Load JSON Data
        $json = @file_get_contents(__DIR__ . '/../data/turkey-locations.json');
        if (!$json) {
            echo json_encode(['success' => false, 'error' => 'Data file not found']);
            exit;
        }
        $jsonData = json_decode($json, true);

        // 3. Find Districts in JSON
        $jsonDistricts = $jsonData['districts'][$provName] ?? [];

        // 4. Get Existing Districts
        $existing = $db->select('locations_district', ['province_id' => $province_id]);
        $existingSlugs = array_column($existing, 'slug');

        $missing = [];
        foreach ($jsonDistricts as $jd) {
            if (!in_array($jd['slug'], $existingSlugs)) {
                $missing[] = $jd;
            }
        }

        echo json_encode(['success' => true, 'missing' => $missing]);

    } elseif ($action === 'add-districts') {
        $province_id = $data['province_id'] ?? '';
        $districts = $data['districts'] ?? [];

        if (empty($province_id) || empty($districts)) {
            echo json_encode(['success' => false, 'error' => 'Missing data']);
            exit;
        }

        $count = 0;
        foreach ($districts as $d) {
            try {
                $exist = $db->select('locations_district', ['province_id' => $province_id, 'slug' => $d['slug']]);
                if (empty($exist)) {
                    $item = [
                        'province_id' => $province_id,
                        'name' => $d['name'],
                        'slug' => $d['slug'],
                        'is_active' => true
                    ];
                    $db->insert('locations_district', $item);
                    $count++;
                }
            } catch (Exception $e) {
            }
        }

        echo json_encode(['success' => true, 'added_count' => $count]);

    } elseif ($action === 'get-missing-towns') {
        $district_id = $data['district_id'] ?? '';

        // 1. Get District
        $dist = $db->select('locations_district', ['id' => $district_id]);
        if (empty($dist)) {
            echo json_encode(['success' => false, 'error' => 'District not found']);
            exit;
        }
        $districtName = $dist[0]['name'];
        $provinceId = $dist[0]['province_id'];

        $prov = $db->select('locations_province', ['id' => $provinceId]);
        if (empty($prov)) {
            echo json_encode(['success' => false, 'error' => 'Province not found']);
            exit;
        }
        $provinceName = $prov[0]['name'];

        // 2. Load Towns JSON
        $jsonFile = __DIR__ . '/../data/turkey-towns.json';
        if (!file_exists($jsonFile)) {
            echo json_encode(['success' => true, 'missing' => []]);
            exit;
        }

        $json = file_get_contents($jsonFile);
        $jsonData = json_decode($json, true);

        // 3. Find Towns in JSON
        $jsonTowns = $jsonData[$provinceName][$districtName] ?? [];

        // 4. Get Existing Towns
        $existing = $db->select('locations_town', ['district_id' => $district_id]);
        $existingSlugs = array_column($existing, 'slug');

        $missing = [];
        foreach ($jsonTowns as $t) {
            if (!in_array($t['slug'], $existingSlugs)) {
                $missing[] = $t;
            }
        }

        echo json_encode(['success' => true, 'missing' => $missing]);

    } elseif ($action === 'add-towns') {
        $district_id = $data['district_id'] ?? '';
        $towns = $data['towns'] ?? [];

        if (empty($district_id) || empty($towns)) {
            echo json_encode(['success' => false, 'error' => 'Missing data']);
            exit;
        }

        $count = 0;
        foreach ($towns as $t) {
            try {
                $exist = $db->select('locations_town', ['district_id' => $district_id, 'slug' => $t['slug']]);
                if (empty($exist)) {
                    $item = [
                        'district_id' => $district_id,
                        'name' => $t['name'],
                        'slug' => $t['slug'],
                        'is_active' => true
                    ];
                    $db->insert('locations_town', $item);
                    $count++;
                }
            } catch (Exception $e) {
            }
        }

        echo json_encode(['success' => true, 'added_count' => $count]);

    } elseif ($action === 'bulk-delete') {
        $table = $data['table'] ?? '';
        $ids = $data['ids'] ?? [];

        if (empty($table) || empty($ids) || !is_array($ids)) {
            throw new Exception('Missing or invalid parameters for bulk delete');
        }

        $deleted_count = 0;
        foreach ($ids as $id) {
            // Cascading Logic
            if ($table === 'locations_province') {
                $db->delete('locations_district', ['province_id' => $id]);
            }
            if ($table === 'locations_district') {
                $db->delete('locations_town', ['district_id' => $id]);
            }

            if ($db->delete($table, ['id' => $id])) {
                $deleted_count++;
            }
        }

        echo json_encode(['success' => true, 'deleted_count' => $deleted_count]);

    } elseif ($action === 'delete') {
        $table = $data['table'] ?? '';
        $id = $data['id'] ?? '';

        if (empty($table) || empty($id)) {
            throw new Exception('Missing required parameters for delete');
        }

        // Cascading Delete
        if ($table === 'locations_province') {
            $db->delete('locations_district', ['province_id' => $id]);
        }
        if ($table === 'locations_district') {
            $db->delete('locations_town', ['district_id' => $id]);
        }

        $result = $db->delete($table, ['id' => $id]);
        echo json_encode(['success' => true, 'data' => $result]);

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
            'post_type' => $post_type,
            'post_status' => $post_status,
            'gallery_folder_id' => !empty($data['gallery_folder_id']) ? $data['gallery_folder_id'] : null,
            'updated_at' => date('Y-m-d H:i:s')
        ];

        if ($id) {
            $db->update('posts', $dbData, ['id' => $id]);
            $resultId = $id;
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
            $db->insert('posts', $dbData);
            $resultId = $dbData['id'];
        }

        echo json_encode(['success' => true, 'id' => $resultId]);

    } else {
        throw new Exception('Invalid action');
    }

} catch (Exception $e) {
    http_response_code(400);
    error_log("API Error: " . $e->getMessage() . " in " . $e->getFile() . ":" . $e->getLine());
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}