<?php
/**
 * Admin Data API
 * Provides data for admin panel (provinces, districts, services, etc.)
 */

session_start();
header('Content-Type: application/json; charset=utf-8');

// Check authentication
if (!isset($_SESSION['admin_user_id'])) {
    http_response_code(401);
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

require_once __DIR__ . '/../includes/database.php';

$method = $_SERVER['REQUEST_METHOD'];
$resource = $_GET['resource'] ?? '';

if ($method !== 'GET') {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);
    exit;
}

try {
    $db = new DatabaseClient();

    switch ($resource) {
        case 'provinces':
            $limit = isset($_GET['limit']) ? (int) $_GET['limit'] : 1000;
            $offset = isset($_GET['offset']) ? (int) $_GET['offset'] : 0;

            $provinces = $db->select('locations_province', [
                'order' => 'name',
                'limit' => $limit,
                'offset' => $offset
            ]);

            $total = count($db->select('locations_province'));

            // Add SEO page info for each province (from posts table)
            $seoPages = $db->select('posts', [
                'post_type' => 'seo_page',
                'post_status' => 'publish'
            ]);

            // Create a map of province_id (from meta) => post array
            $seoPagesMap = [];
            foreach ($seoPages as $post) {
                // We'd ideally need post_meta for this, but to keep it simple and backwards compatible
                // we'll assume the migration stored province_id in post_meta
                $meta = $db->select('post_meta', ['post_id' => $post['id'], 'meta_key' => 'province_id']);
                if (!empty($meta)) {
                    $provinceId = json_decode($meta[0]['meta_value'], true);
                    if (!isset($seoPagesMap[$provinceId])) {
                        $seoPagesMap[$provinceId] = [];
                    }
                    $seoPagesMap[$provinceId][] = $post;
                }
            }

            // Add seo_pages to each province
            foreach ($provinces as &$province) {
                $province['seo_pages'] = $seoPagesMap[$province['id']] ?? [];
            }
            unset($province);

            echo json_encode([
                'data' => $provinces,
                'total' => $total
            ]);
            break;

        case 'districts':
            $provinceId = $_GET['province_id'] ?? null;
            $limit = isset($_GET['limit']) ? (int) $_GET['limit'] : 1000;
            $offset = isset($_GET['offset']) ? (int) $_GET['offset'] : 0;

            $params = ['order' => 'name', 'limit' => $limit, 'offset' => $offset];
            $countParams = [];
            if ($provinceId) {
                $params['province_id'] = $provinceId;
                $countParams['province_id'] = $provinceId;
            }
            $districts = $db->select('locations_district', $params);
            $total = count($db->select('locations_district', $countParams));

            // Add province name to each district
            $provinces = $db->select('locations_province');
            $provinceMap = [];
            foreach ($provinces as $province) {
                $provinceMap[$province['id']] = $province['name'];
            }

            // Add SEO page info for each district (from posts table)
            $seoPagesMap = [];
            $posts = $db->select('posts', ['post_type' => 'seo_page']);
            foreach ($posts as $post) {
                $meta = $db->select('post_meta', ['post_id' => $post['id'], 'meta_key' => 'district_id']);
                if (!empty($meta)) {
                    $districtId = json_decode($meta[0]['meta_value'], true);
                    if (!isset($seoPagesMap[$districtId])) {
                        $seoPagesMap[$districtId] = [];
                    }
                    $seoPagesMap[$districtId][] = $post;
                }
            }

            foreach ($districts as &$district) {
                $district['province_name'] = $provinceMap[$district['province_id']] ?? '';
                $district['seo_pages'] = $seoPagesMap[$district['id']] ?? [];
            }
            unset($district);

            echo json_encode([
                'data' => $districts,
                'total' => $total
            ]);
            break;

        case 'services':
            $services = $db->select('posts', [
                'post_type' => 'service',
                'order' => 'title'
            ]);
            // Map title to name for backward compatibility
            foreach ($services as &$service) {
                $service['name'] = $service['title'];
                $service['is_active'] = ($service['post_status'] === 'publish');
            }
            echo json_encode($services);
            break;

        case 'seo-pages':
            $limit = isset($_GET['limit']) ? (int) $_GET['limit'] : 1000;
            $offset = isset($_GET['offset']) ? (int) $_GET['offset'] : 0;

            $pages = $db->select('posts', [
                'post_type' => 'seo_page',
                'order' => 'updated_at DESC',
                'limit' => $limit,
                'offset' => $offset
            ]);
            $total = count($db->select('posts', ['post_type' => 'seo_page']));

            // Map post_type to type for backward compatibility
            foreach ($pages as &$page) {
                $page['type'] = $page['post_type']; // Should be enriched from meta if needed, but 'seo_page' is generic
                $page['published'] = ($page['post_status'] === 'publish');

                // Try to get more specific type from meta
                $meta = $db->select('post_meta', ['post_id' => $page['id'], 'meta_key' => 'province_id']);
                if (!empty($meta))
                    $page['type'] = 'province';
                $meta = $db->select('post_meta', ['post_id' => $page['id'], 'meta_key' => 'district_id']);
                if (!empty($meta))
                    $page['type'] = 'district';
            }

            echo json_encode([
                'data' => $pages,
                'total' => $total
            ]);
            break;

        case 'media':
            $media = $db->select('media', [
                'order' => 'created_at DESC'
            ]);
            echo json_encode($media);
            break;

        case 'stats':
            $totalProvinces = count($db->select('locations_province'));
            $activeProvinces = count($db->select('locations_province', ['is_active' => true]));
            $totalDistricts = count($db->select('locations_district'));
            $activeDistricts = count($db->select('locations_district', ['is_active' => true]));
            $publishedPages = count($db->select('posts', ['post_type' => 'seo_page', 'post_status' => 'publish']));

            echo json_encode([
                'totalProvinces' => $totalProvinces,
                'activeProvinces' => $activeProvinces,
                'totalDistricts' => $totalDistricts,
                'activeDistricts' => $activeDistricts,
                'publishedPages' => $publishedPages
            ]);
            break;

        default:
            http_response_code(400);
            echo json_encode(['error' => 'Invalid resource']);
    }

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}

