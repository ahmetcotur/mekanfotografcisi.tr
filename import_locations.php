<?php
/**
 * Data Import Script: Fetches Turkey location data from GitHub and populates the database
 */
require_once __DIR__ . '/includes/database.php';
require_once __DIR__ . '/includes/helpers.php';

$db = new DatabaseClient();

$baseUrl = "https://raw.githubusercontent.com/muratgozel/turkey-neighbourhoods/master/src/data/";

function fetchData($url)
{
    echo "Fetching: $url\n";
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    $output = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($httpCode !== 200) {
        throw new Exception("Failed to fetch data from $url (HTTP $httpCode)");
    }

    return json_decode($output, true);
}

try {
    // 1. Fetch Cities
    echo "Processing Cities...\n";
    $citiesByCode = fetchData($baseUrl . "cityNamesByCode.json");

    $provinceMap = []; // code => uuid

    foreach ($citiesByCode as $code => $name) {
        $slug = to_permalink($name);

        // Find existing or create new
        $existing = $db->query("SELECT id FROM locations_province WHERE plate_code = :code OR name = :name", [
            'code' => $code,
            'name' => $name
        ]);

        if (!empty($existing)) {
            $provinceId = $existing[0]['id'];
            $db->update('locations_province', [
                'plate_code' => $code,
                'name' => $name,
                'slug' => $slug,
                'updated_at' => date('Y-m-d H:i:s')
            ], ['id' => $provinceId]);
        } else {
            $provinceId = sprintf(
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
            $db->insert('locations_province', [
                'id' => $provinceId,
                'name' => $name,
                'slug' => $slug,
                'plate_code' => $code,
                'is_active' => true,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            ]);
        }
        $provinceMap[$code] = $provinceId;
    }

    // 2. Fetch Districts
    echo "Processing Districts...\n";
    $districtsByCityCode = fetchData($baseUrl . "districtsByCityCode.json");

    $districtMap = []; // province_id:name => uuid

    foreach ($districtsByCityCode as $cityCode => $districts) {
        if (!isset($provinceMap[$cityCode]))
            continue;
        $provinceId = $provinceMap[$cityCode];

        foreach ($districts as $districtName) {
            $slug = to_permalink($districtName);

            $existing = $db->query("SELECT id FROM locations_district WHERE province_id = :p_id AND name = :name", [
                'p_id' => $provinceId,
                'name' => $districtName
            ]);

            if (!empty($existing)) {
                $districtId = $existing[0]['id'];
            } else {
                $districtId = sprintf(
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
                $db->insert('locations_district', [
                    'id' => $districtId,
                    'province_id' => $provinceId,
                    'name' => $districtName,
                    'slug' => $slug,
                    'is_active' => true,
                    'created_at' => date('Y-m-d H:i:s'),
                    'updated_at' => date('Y-m-d H:i:s')
                ]);
            }
            $districtMap["$provinceId:$districtName"] = $districtId;
        }
    }

    // 3. Fetch Neighborhoods (Large File)
    echo "Processing Neighborhoods (this might take a while)...\n";
    $neighbourhoodsData = fetchData($baseUrl . "neighbourhoodsByDistrictAndCityCode.json");

    foreach ($neighbourhoodsData as $cityCode => $districts) {
        if (!isset($provinceMap[$cityCode]))
            continue;
        $provinceId = $provinceMap[$cityCode];

        foreach ($districts as $districtName => $neighbourhoods) {
            $key = "$provinceId:$districtName";
            if (!isset($districtMap[$key]))
                continue;
            $districtId = $districtMap[$key];

            foreach ($neighbourhoods as $nName) {
                // To keep it efficient, we check existence by district_id and name
                $baseSlug = to_permalink($nName);
                $slug = $baseSlug;

                $existing = $db->query("SELECT id FROM locations_town WHERE district_id = :d_id AND name = :name", [
                    'd_id' => $districtId,
                    'name' => $nName
                ]);

                if (empty($existing)) {
                    // Check if slug already exists in this district
                    $checkSlug = $db->query("SELECT id FROM locations_town WHERE district_id = :d_id AND slug = :slug", [
                        'd_id' => $districtId,
                        'slug' => $slug
                    ]);

                    $counter = 1;
                    while (!empty($checkSlug)) {
                        $slug = $baseSlug . "-" . $counter;
                        $checkSlug = $db->query("SELECT id FROM locations_town WHERE district_id = :d_id AND slug = :slug", [
                            'd_id' => $districtId,
                            'slug' => $slug
                        ]);
                        $counter++;
                    }

                    $id = sprintf(
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
                    $db->insert('locations_town', [
                        'id' => $id,
                        'district_id' => $districtId,
                        'name' => $nName,
                        'slug' => $slug,
                        'is_active' => false,
                        'created_at' => date('Y-m-d H:i:s'),
                        'updated_at' => date('Y-m-d H:i:s')
                    ]);
                }
            }
        }
    }

    // 4. City Distances
    echo "Processing City Distances...\n";
    $distances = fetchData($baseUrl . "distances.json");

    foreach ($distances as $fromCode => $targets) {
        if (!isset($provinceMap[$fromCode]))
            continue;
        $fromId = $provinceMap[$fromCode];

        foreach ($targets as $toCode => $km) {
            if (!isset($provinceMap[$toCode]))
                continue;
            $toId = $provinceMap[$toCode];

            // Upsert distance
            $existing = $db->query("SELECT id FROM locations_city_distance WHERE province_from_id = :f AND province_to_id = :t", [
                'f' => $fromId,
                't' => $toId
            ]);

            if (!empty($existing)) {
                $db->update('locations_city_distance', ['distance_km' => $km], ['id' => $existing[0]['id']]);
            } else {
                $db->insert('locations_city_distance', [
                    'province_from_id' => $fromId,
                    'province_to_id' => $toId,
                    'distance_km' => $km
                ]);
            }
        }
    }

    echo "\nImport completed successfully!\n";

} catch (Exception $e) {
    echo "\nError during import: " . $e->getMessage() . "\n";
    exit(1);
}
