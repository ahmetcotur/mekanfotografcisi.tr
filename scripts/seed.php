<?php
/**
 * Database Seeding Script
 * Populates database with initial data (locations, services)
 */

require_once __DIR__ . '/../includes/database.php';

// Helper function to create slug from Turkish text
function createSlug($text) {
    // First replace Turkish characters BEFORE lowercasing
    $text = str_replace(
        ['Ğ', 'Ü', 'Ş', 'İ', 'Ö', 'Ç', 'ğ', 'ü', 'ş', 'ı', 'ö', 'ç'],
        ['g', 'u', 's', 'i', 'o', 'c', 'g', 'u', 's', 'i', 'o', 'c'],
        $text
    );
    // Then convert to lowercase
    $text = mb_strtolower($text, 'UTF-8');
    // Replace non-alphanumeric characters with hyphens
    $text = preg_replace('/[^a-z0-9]+/', '-', $text);
    // Trim hyphens from start and end
    $text = trim($text, '-');
    return $text;
}

echo "Starting database seeding...\n\n";

$db = new DatabaseClient();
$connection = $db->getConnection();

try {
    $connection->beginTransaction();
    
    // =============================================
    // 1. SEED PROVINCES
    // =============================================
    echo "→ Seeding provinces...\n";
    
    $jsonPath = __DIR__ . '/../data/turkey-locations.json';
    if (!file_exists($jsonPath)) {
        throw new Exception("Location data file not found: $jsonPath");
    }
    
    $locationData = json_decode(file_get_contents($jsonPath), true);
    if (!$locationData) {
        throw new Exception("Failed to parse location data JSON");
    }
    
    $provinceMap = []; // name => id mapping
    $provinceCount = 0;
    
    // Collect all unique provinces from regions
    $allProvinces = [];
    foreach ($locationData['regions'] ?? [] as $region) {
        foreach ($region['provinces'] ?? [] as $province) {
            $name = $province['name'];
            // Skip duplicates
            if (!isset($allProvinces[$name])) {
                $allProvinces[$name] = [
                    'name' => $name,
                    'plate_code' => $province['plate_code'] ?? null,
                    'region_name' => $region['name']
                ];
            }
        }
    }
    
    // Insert provinces
    foreach ($allProvinces as $province) {
        $slug = createSlug($province['name']);
        
        // Check if already exists
        $existing = $db->select('locations_province', ['slug' => 'eq.' . $slug]);
        if (!empty($existing)) {
            $provinceMap[$province['name']] = $existing[0]['id'];
            continue;
        }
        
        $result = $db->insert('locations_province', [
            'name' => $province['name'],
            'slug' => $slug,
            'region_name' => $province['region_name'],
            'plate_code' => $province['plate_code'],
            'is_active' => true
        ]);
        
        $provinceMap[$province['name']] = $result['id'];
        $provinceCount++;
    }
    
    echo "  ✓ Inserted $provinceCount provinces\n";
    
    // =============================================
    // 2. SEED DISTRICTS
    // =============================================
    echo "→ Seeding districts...\n";
    
    $districtCount = 0;
    foreach ($locationData['districts'] ?? [] as $provinceName => $districts) {
        $provinceId = $provinceMap[$provinceName] ?? null;
        if (!$provinceId) {
            echo "  ⚠ Warning: Province '$provinceName' not found, skipping districts\n";
            continue;
        }
        
        foreach ($districts as $district) {
            $name = $district['name'];
            $slug = $district['slug'] ?? createSlug($name);
            
            // Check if already exists
            $existing = $db->select('locations_district', [
                'province_id' => $provinceId,
                'slug' => $slug
            ]);
            if (!empty($existing)) {
                continue;
            }
            
            // Special local notes for popular districts
            $localNotes = null;
            $notesMap = [
                'Kaş' => 'Butik oteller ve lüks villalar için ideal lokasyon',
                'Kalkan' => 'Lüks villa fotoğrafçılığında uzmanlaştığımız bölge',
                'Bodrum' => 'Marina ve lüks villa projelerinde deneyimli',
                'Fethiye' => 'Doğal güzellikler ve butik oteller',
                'Marmaris' => 'Turizm tesisleri ve resort oteller',
                'Alanya' => 'Konut projeleri ve otel kompleksleri'
            ];
            if (isset($notesMap[$name])) {
                $localNotes = $notesMap[$name];
            }
            
            $db->insert('locations_district', [
                'province_id' => $provinceId,
                'name' => $name,
                'slug' => $slug,
                'is_active' => true,
                'local_notes' => $localNotes
            ]);
            
            $districtCount++;
        }
    }
    
    echo "  ✓ Inserted $districtCount districts\n";
    
    // =============================================
    // 3. SEED SERVICES
    // =============================================
    echo "→ Seeding services...\n";
    
    $services = [
        [
            'name' => 'Mimari Fotoğrafçılık',
            'slug' => 'mimari-fotografcilik',
            'short_intro' => 'Binaların dış cephe, peyzaj ve çevre düzenlemelerini en etkileyici açılardan fotoğraflıyoruz.',
            'is_active' => true
        ],
        [
            'name' => 'İç Mekan Fotoğrafçılığı',
            'slug' => 'ic-mekan-fotografciligi',
            'short_intro' => 'Ev, villa, ofis ve ticari alanların iç mekan fotoğraflarını profesyonel ekipmanlarla çekiyoruz.',
            'is_active' => true
        ],
        [
            'name' => 'Emlak Fotoğrafçılığı',
            'slug' => 'emlak-fotografciligi',
            'short_intro' => 'Satılık veya kiralık mülklerinizi en çekici şekilde göstererek pazarlama sürecinize katkı sağlıyoruz.',
            'is_active' => true
        ],
        [
            'name' => 'Otel ve Restoran Fotoğrafçılığı',
            'slug' => 'otel-restoran-fotografciligi',
            'short_intro' => 'Otel odaları, restoranlar ve cafe mekanları için müşteri çekici fotoğraflar üretiyoruz.',
            'is_active' => true
        ],
        [
            'name' => 'Butik Otel Fotoğrafçılığı',
            'slug' => 'butik-otel-fotografciligi',
            'short_intro' => 'Küçük ölçekli, özel karakterli butik oteller için özel fotoğrafçılık hizmetleri.',
            'is_active' => true
        ],
        [
            'name' => 'Yemek Fotoğrafçılığı',
            'slug' => 'yemek-fotografciligi',
            'short_intro' => 'Restoran ve cafe menüleri için profesyonel yemek ve gastronomi fotoğrafçılığı.',
            'is_active' => true
        ],
        [
            'name' => 'Lifestyle Fotoğrafçılığı',
            'slug' => 'lifestyle-fotografciligi',
            'short_intro' => 'Yaşam tarzını yansıtan, hikaye anlatan profesyonel lifestyle fotoğrafçılığı.',
            'is_active' => true
        ],
        [
            'name' => 'Villa Fotoğrafçılığı',
            'slug' => 'villa-fotografciligi',
            'short_intro' => 'Lüks villaların tüm detaylarını profesyonel fotoğraflarla ölümsüzleştiriyoruz.',
            'is_active' => true
        ],
        [
            'name' => 'Otel Fotoğrafçılığı',
            'slug' => 'otel-fotografciligi',
            'short_intro' => 'Otel, resort ve tatil köylerinin tüm alanlarını profesyonel fotoğraflarla belgeliyoruz.',
            'is_active' => true
        ],
        [
            'name' => 'Yat Fotoğrafçılığı',
            'slug' => 'yat-fotografciligi',
            'short_intro' => 'Lüks yatların iç ve dış mekanlarını profesyonel fotoğraflarla çekiyoruz.',
            'is_active' => true
        ],
        [
            'name' => 'Konut Projeleri Fotoğrafçılığı',
            'slug' => 'konut-projeleri-fotografciligi',
            'short_intro' => 'Konut kompleksleri ve rezidans projeleri için pazarlama odaklı profesyonel fotoğrafçılık.',
            'is_active' => true
        ],
        [
            'name' => 'Ofis Fotoğrafçılığı',
            'slug' => 'ofis-fotografciligi',
            'short_intro' => 'Kurumsal ofislerin modern ve profesyonel görünümünü fotoğraflarla yansıtıyoruz.',
            'is_active' => true
        ],
        [
            'name' => 'İş Merkezi Fotoğrafçılığı',
            'slug' => 'is-merkezi-fotografciligi',
            'short_intro' => 'İş merkezleri ve ticari komplekslerin profesyonel görünümünü fotoğraflarla yansıtıyoruz.',
            'is_active' => true
        ],
        [
            'name' => 'Ticari Alan Fotoğrafçılığı',
            'slug' => 'ticari-alan-fotografciligi',
            'short_intro' => 'Mağaza, showroom ve ticari işletmelerin çekici görsellerini profesyonelce üretiyoruz.',
            'is_active' => true
        ],
        [
            'name' => 'Pansiyon Fotoğrafçılığı',
            'slug' => 'pansiyon-fotografciligi',
            'short_intro' => 'Pansiyon ve butik konaklama tesislerinin samimi atmosferini fotoğraflarla gösteriyoruz.',
            'is_active' => true
        ],
        [
            'name' => 'Termal Tesis Fotoğrafçılığı',
            'slug' => 'termal-tesis-fotografciligi',
            'short_intro' => 'Termal oteller ve spa tesislerinin sağlık ve dinlenme alanlarını profesyonelce çekiyoruz.',
            'is_active' => true
        ]
    ];
    
    $serviceCount = 0;
    foreach ($services as $service) {
        // Check if already exists
        $existing = $db->select('services', ['slug' => 'eq.' . $service['slug']]);
        if (!empty($existing)) {
            continue;
        }
        
        $db->insert('services', $service);
        $serviceCount++;
    }
    
    echo "  ✓ Inserted $serviceCount services\n";
    
    $connection->commit();
    
    echo "\n";
    echo "✓ Seeding complete!\n";
    echo "  Provinces: " . count($provinceMap) . "\n";
    echo "  Districts: $districtCount\n";
    echo "  Services: $serviceCount\n";
    
} catch (Exception $e) {
    $connection->rollBack();
    echo "\n✗ Error: " . $e->getMessage() . "\n";
    exit(1);
}

