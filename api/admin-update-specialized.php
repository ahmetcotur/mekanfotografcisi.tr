<?php
/**
 * Specialized actions for admin-update.php
 * Extracted to keep the main file clean
 */

if ($action === 'get-missing-districts') {
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
    exit;

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
                    'id' => sprintf('%04x%04x-%04x-%04x-%04x-%04x%04x%04x', mt_rand(0, 0xffff), mt_rand(0, 0xffff), mt_rand(0, 0xffff), mt_rand(0, 0x0fff) | 0x4000, mt_rand(0, 0x3fff) | 0x8000, mt_rand(0, 0xffff), mt_rand(0, 0xffff), mt_rand(0, 0xffff)),
                    'province_id' => $province_id,
                    'name' => $d['name'],
                    'slug' => $d['slug'],
                    'is_active' => true,
                    'created_at' => date('Y-m-d H:i:s')
                ];
                $db->insert('locations_district', $item);
                $count++;
            }
        } catch (Exception $e) {
        }
    }

    echo json_encode(['success' => true, 'added_count' => $count]);
    exit;

} elseif ($action === 'get-missing-towns') {
    $district_id = $data['district_id'] ?? '';
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

    $jsonFile = __DIR__ . '/../data/turkey-towns.json';
    if (!file_exists($jsonFile)) {
        echo json_encode(['success' => true, 'missing' => []]);
        exit;
    }

    $json = file_get_contents($jsonFile);
    $jsonData = json_decode($json, true);
    $jsonTowns = $jsonData[$provinceName][$districtName] ?? [];

    $existing = $db->select('locations_town', ['district_id' => $district_id]);
    $existingSlugs = array_column($existing, 'slug');

    $missing = [];
    foreach ($jsonTowns as $t) {
        if (!in_array($t['slug'], $existingSlugs)) {
            $missing[] = $t;
        }
    }

    echo json_encode(['success' => true, 'missing' => $missing]);
    exit;

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
                    'id' => sprintf('%04x%04x-%04x-%04x-%04x-%04x%04x%04x', mt_rand(0, 0xffff), mt_rand(0, 0xffff), mt_rand(0, 0xffff), mt_rand(0, 0x0fff) | 0x4000, mt_rand(0, 0x3fff) | 0x8000, mt_rand(0, 0xffff), mt_rand(0, 0xffff), mt_rand(0, 0xffff)),
                    'district_id' => $district_id,
                    'name' => $t['name'],
                    'slug' => $t['slug'],
                    'is_active' => true,
                    'created_at' => date('Y-m-d H:i:s')
                ];
                $db->insert('locations_town', $item);
                $count++;
            }
        } catch (Exception $e) {
        }
    }
    echo json_encode(['success' => true, 'added_count' => $count]);
    exit;

} else {
    throw new Exception('Invalid action');
}
