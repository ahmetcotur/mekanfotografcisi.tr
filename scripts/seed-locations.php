<?php
/**
 * Turkey Locations Seed Script (PHP)
 * Seeds all 81 provinces and 973 districts into PostgreSQL
 * Usage: php scripts/seed-locations.php
 */

require_once __DIR__ . '/../includes/database.php';
require_once __DIR__ . '/../includes/helpers.php';

function createSlug($text)
{
    return to_permalink($text); // Using existing helper
}

function log_message($msg)
{
    if (php_sapi_name() === 'cli') {
        echo $msg;
    } else {
        error_log("[Seeder] " . trim($msg));
    }
}

function main()
{
    log_message("🚀 Starting Turkey locations seed process...\n");

    try {
        $db = new DatabaseClient();

        // Load location data
        $dataPath = __DIR__ . '/../data/turkey-locations.json';
        if (!file_exists($dataPath)) {
            throw new Exception("Location data file not found: $dataPath");
        }

        $locationData = json_decode(file_get_contents($dataPath), true);

        // 1. Seed Provinces
        log_message("🏛️  Seeding provinces...\n");
        $provinces = [];
        $allProvinces = [];

        foreach ($locationData['regions'] as $region) {
            foreach ($region['provinces'] as $province) {
                if (!in_array($province['name'], $allProvinces)) {
                    $allProvinces[] = $province['name'];
                    $slug = createSlug($province['name']);

                    // Check if exists
                    $exists = $db->select('locations_province', ['slug' => $slug]);
                    if (empty($exists)) {
                        $db->insert('locations_province', [
                            'name' => $province['name'],
                            'slug' => $slug,
                            'region_name' => $region['name'],
                            'plate_code' => $province['plate_code'],
                            'is_active' => 'false'
                        ]);
                    }
                }
            }
        }
        log_message(" ✅ Provinces seeded.\n");

        // 2. Seed Districts
        log_message("🏘️  Seeding districts...\n");
        $dbProvinces = $db->select('locations_province');
        $provinceMap = [];
        foreach ($dbProvinces as $p) {
            $provinceMap[$p['name']] = $p['id'];
        }

        $districtCount = 0;
        foreach ($locationData['districts'] as $provinceName => $districtList) {
            $provinceId = $provinceMap[$provinceName] ?? null;
            if (!$provinceId)
                continue;

            foreach ($districtList as $district) {
                $slug = $district['slug'] ?? createSlug($district['name']);

                $exists = $db->select('locations_district', [
                    'province_id' => $provinceId,
                    'slug' => $slug
                ]);

                if (empty($exists)) {
                    $db->insert('locations_district', [
                        'province_id' => $provinceId,
                        'name' => $district['name'],
                        'slug' => $slug,
                        'is_active' => 'false'
                    ]);
                    $districtCount++;
                }
            }
        }
        log_message(" ✅ $districtCount new districts seeded.\n");

        // 3. Seed Default Services
        log_message("🛠️  Seeding default services...\n");
        $services = [
            ['title' => 'Mimari Fotoğrafçılık', 'slug' => 'mimari-fotografcilik', 'content' => 'Binaların dış cephe, peyzaj ve çevre düzenlemelerini en etkileyici açılardan fotoğraflıyoruz.'],
            ['title' => 'İç Mekan Fotoğrafçılığı', 'slug' => 'ic-mekan-fotografciligi', 'content' => 'Ev, villa, ofis ve ticari alanların iç mekan fotoğraflarını profesyonel ekipmanlarla çekiyoruz.'],
            ['title' => 'Emlak Fotoğrafçılığı', 'slug' => 'emlak-fotografciligi', 'content' => 'Satılık veya kiralık mülklerinizi en çekici şekilde göstererek pazarlama sürecinize katkı sağlıyoruz.'],
            ['title' => 'Otel ve Restoran Fotoğrafçılığı', 'slug' => 'otel-restoran-fotografciligi', 'content' => 'Otel odaları, restoranlar ve cafe mekanları için müşteri çekici fotoğraflar üretiyoruz.'],
        ];

        foreach ($services as $service) {
            $exists = $db->select('posts', ['slug' => $service['slug'], 'post_type' => 'service']);
            if (empty($exists)) {
                $db->insert('posts', [
                    'id' => sprintf('%04x%04x-%04x-%04x-%04x-%04x%04x%04x', mt_rand(0, 0xffff), mt_rand(0, 0xffff), mt_rand(0, 0xffff), mt_rand(0, 0x0fff) | 0x4000, mt_rand(0, 0x3fff) | 0x8000, mt_rand(0, 0xffff), mt_rand(0, 0xffff), mt_rand(0, 0xffff)),
                    'title' => $service['title'],
                    'slug' => $service['slug'],
                    'content' => $service['content'],
                    'post_type' => 'service',
                    'post_status' => 'publish',
                    'created_at' => date('Y-m-d H:i:s'),
                    'updated_at' => date('Y-m-d H:i:s')
                ]);
            }
        }
        log_message(" ✅ Services seeded.\n");

        log_message("\n✨ Seed operation completed successfully!\n");

    } catch (Exception $e) {
        log_message("❌ Error: " . $e->getMessage() . "\n");
        if (php_sapi_name() === 'cli') {
            exit(1);
        }
    }
}

main();
