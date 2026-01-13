<?php
/**
 * Database Client Wrapper
 * Maintains compatibility with existing code while using PostgreSQL
 */

// Load configuration
require_once __DIR__ . '/config.php';

// Use PostgreSQL database if configured, otherwise fallback to mock data
$usePostgreSQL = (!empty(env('DB_HOST')) && !empty(env('DB_NAME'))) ||
    (!empty(env('NEXT_PUBLIC_SUPABASE_URL')) && !empty(env('DB_PASSWORD')));

if ($usePostgreSQL) {
    require_once __DIR__ . '/database.php';

    // Create a wrapper class that mimics SupabaseClient interface
    class SupabaseClient
    {
        private $db;

        public function __construct($url = null, $key = null)
        {
            global $db;
            if (!isset($db)) {
                try {
                    $db = new DatabaseClient();
                } catch (Exception $e) {
                    error_log("Failed to initialize database: " . $e->getMessage());
                    $db = null;
                }
            }
            $this->db = $db;
        }

        /**
         * Select data from database
         */
        public function select($table, $params = [])
        {
            try {
                // Use PostgreSQL database
                return $this->db->select($table, $params);
            } catch (Exception $e) {
                // Fallback to mock data if database is not available
                error_log("Database query failed, using mock data: " . $e->getMessage());
                return $this->getMockData($table, $params);
            }
        }

        /**
         * Get mock data as fallback
         */
        private function getMockData($table, $params = [])
        {
            switch ($table) {
                case 'locations_province':
                    return $this->getMockProvinces($params);
                case 'locations_district':
                    return $this->getMockDistricts($params);
                case 'services':
                    return $this->getMockServices($params);
                case 'portfolio_projects':
                    return $this->getMockPortfolio($params);
                case 'seo_pages':
                    return $this->getMockSeoPages($params);
                default:
                    return [];
            }
        }

        /**
         * Load location data from JSON file
         */
        private function loadLocationData()
        {
            static $locationData = null;

            if ($locationData === null) {
                $jsonPath = __DIR__ . '/../data/turkey-locations.json';
                if (file_exists($jsonPath)) {
                    $jsonContent = file_get_contents($jsonPath);
                    $locationData = json_decode($jsonContent, true);
                } else {
                    $locationData = ['regions' => [], 'districts' => []];
                }
            }

            return $locationData;
        }

        /**
         * Create slug from Turkish text
         */
        private function createSlug($text)
        {
            // First replace Turkish characters BEFORE lowercasing to avoid issues with İ
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

        private function getMockProvinces($params = [])
        {
            $locationData = $this->loadLocationData();
            $provinces = [];
            $idCounter = 1;
            $provinceMap = []; // name => id mapping

            // Build provinces from JSON data
            foreach ($locationData['regions'] ?? [] as $region) {
                foreach ($region['provinces'] ?? [] as $province) {
                    $name = $province['name'];
                    $slug = $this->createSlug($name);

                    // Skip duplicates (some provinces appear in multiple regions)
                    if (isset($provinceMap[$name])) {
                        continue;
                    }

                    $provinceMap[$name] = (string) $idCounter;

                    $provinces[] = [
                        'id' => (string) $idCounter,
                        'name' => $name,
                        'slug' => $slug,
                        'region_name' => $region['name'],
                        'plate_code' => $province['plate_code'] ?? null,
                        'is_active' => true
                    ];

                    $idCounter++;
                }
            }

            // Apply filters
            if (isset($params['is_active'])) {
                $isActive = $params['is_active'] === 'eq.true';
                $provinces = array_filter($provinces, function ($p) use ($isActive) {
                    return $p['is_active'] === $isActive;
                });
            }

            if (isset($params['slug'])) {
                $slug = str_replace('eq.', '', $params['slug']);
                $provinces = array_filter($provinces, function ($p) use ($slug) {
                    return $p['slug'] === $slug;
                });
            }

            return array_values($provinces);
        }

        private function getMockDistricts($params = [])
        {
            $locationData = $this->loadLocationData();
            $districts = [];
            $idCounter = 1;

            // Build province name => id mapping first
            $provinceMap = [];
            $allProvinces = $this->getMockProvinces([]);
            foreach ($allProvinces as $province) {
                $provinceMap[$province['name']] = $province['id'];
            }

            // Build districts from JSON data
            foreach ($locationData['districts'] ?? [] as $provinceName => $provinceDistricts) {
                $provinceId = $provinceMap[$provinceName] ?? null;
                if (!$provinceId)
                    continue;

                $province = null;
                foreach ($allProvinces as $p) {
                    if ($p['name'] === $provinceName) {
                        $province = $p;
                        break;
                    }
                }

                foreach ($provinceDistricts as $district) {
                    $name = $district['name'];
                    $slug = $district['slug'] ?? $this->createSlug($name);

                    // Special local notes for popular districts
                    $localNotes = null;
                    if (in_array($name, ['Kaş', 'Kalkan', 'Bodrum', 'Fethiye', 'Marmaris', 'Alanya'])) {
                        $notesMap = [
                            'Kaş' => 'Butik oteller ve lüks villalar için ideal lokasyon',
                            'Kalkan' => 'Lüks villa fotoğrafçılığında uzmanlaştığımız bölge',
                            'Bodrum' => 'Marina ve lüks villa projelerinde deneyimli',
                            'Fethiye' => 'Doğal güzellikler ve butik oteller',
                            'Marmaris' => 'Turizm tesisleri ve resort oteller',
                            'Alanya' => 'Konut projeleri ve otel kompleksleri'
                        ];
                        $localNotes = $notesMap[$name] ?? null;
                    }

                    $districts[] = [
                        'id' => (string) $idCounter,
                        'province_id' => $provinceId,
                        'name' => $name,
                        'slug' => $slug,
                        'is_active' => true,
                        'local_notes' => $localNotes,
                        'locations_province' => $province ? [
                            'name' => $province['name'],
                            'slug' => $province['slug']
                        ] : null
                    ];

                    $idCounter++;
                }
            }

            // Apply filters
            if (isset($params['province_id'])) {
                $provinceId = str_replace('eq.', '', $params['province_id']);
                $districts = array_filter($districts, function ($d) use ($provinceId) {
                    return $d['province_id'] === $provinceId;
                });
            }

            if (isset($params['slug'])) {
                $slug = str_replace('eq.', '', $params['slug']);
                $districts = array_filter($districts, function ($d) use ($slug) {
                    return $d['slug'] === $slug;
                });
            }

            if (isset($params['is_active'])) {
                $isActive = $params['is_active'] === 'eq.true';
                $districts = array_filter($districts, function ($d) use ($isActive) {
                    return $d['is_active'] === $isActive;
                });
            }

            return array_values($districts);
        }

        private function getMockServices($params = [])
        {
            $services = [
                [
                    'id' => '1',
                    'name' => 'Mimari Fotoğrafçılık',
                    'slug' => 'mimari-fotografcilik',
                    'short_intro' => 'Binaların dış cephe, peyzaj ve çevre düzenlemelerini en etkileyici açılardan fotoğraflıyoruz.',
                    'is_active' => true
                ],
                [
                    'id' => '2',
                    'name' => 'İç Mekan Fotoğrafçılığı',
                    'slug' => 'ic-mekan-fotografciligi',
                    'short_intro' => 'Ev, villa, ofis ve ticari alanların iç mekan fotoğraflarını profesyonel ekipmanlarla çekiyoruz.',
                    'is_active' => true
                ],
                [
                    'id' => '3',
                    'name' => 'Emlak Fotoğrafçılığı',
                    'slug' => 'emlak-fotografciligi',
                    'short_intro' => 'Satılık veya kiralık mülklerinizi en çekici şekilde göstererek pazarlama sürecinize katkı sağlıyoruz.',
                    'is_active' => true
                ],
                [
                    'id' => '4',
                    'name' => 'Otel ve Restoran Fotoğrafçılığı',
                    'slug' => 'otel-restoran-fotografciligi',
                    'short_intro' => 'Otel odaları, restoranlar ve cafe mekanları için müşteri çekici fotoğraflar üretiyoruz.',
                    'is_active' => true
                ],
                [
                    'id' => '5',
                    'name' => 'Butik Otel Fotoğrafçılığı',
                    'slug' => 'butik-otel-fotografciligi',
                    'short_intro' => 'Küçük ölçekli, özel karakterli butik oteller için özel fotoğrafçılık hizmetleri.',
                    'is_active' => true
                ],
                [
                    'id' => '6',
                    'name' => 'Yemek Fotoğrafçılığı',
                    'slug' => 'yemek-fotografciligi',
                    'short_intro' => 'Restoran ve cafe menüleri için profesyonel yemek ve gastronomi fotoğrafçılığı.',
                    'is_active' => true
                ],
                [
                    'id' => '7',
                    'name' => 'Lifestyle Fotoğrafçılığı',
                    'slug' => 'lifestyle-fotografciligi',
                    'short_intro' => 'Yaşam tarzını yansıtan, hikaye anlatan profesyonel lifestyle fotoğrafçılığı.',
                    'is_active' => true
                ],
                [
                    'id' => '8',
                    'name' => 'Villa Fotoğrafçılığı',
                    'slug' => 'villa-fotografciligi',
                    'short_intro' => 'Lüks villaların tüm detaylarını profesyonel fotoğraflarla ölümsüzleştiriyoruz.',
                    'is_active' => true
                ],
                [
                    'id' => '9',
                    'name' => 'Otel Fotoğrafçılığı',
                    'slug' => 'otel-fotografciligi',
                    'short_intro' => 'Otel, resort ve tatil köylerinin tüm alanlarını profesyonel fotoğraflarla belgeliyoruz.',
                    'is_active' => true
                ],
                [
                    'id' => '10',
                    'name' => 'Yat Fotoğrafçılığı',
                    'slug' => 'yat-fotografciligi',
                    'short_intro' => 'Lüks yatların iç ve dış mekanlarını profesyonel fotoğraflarla çekiyoruz.',
                    'is_active' => true
                ],
                [
                    'id' => '11',
                    'name' => 'Konut Projeleri Fotoğrafçılığı',
                    'slug' => 'konut-projeleri-fotografciligi',
                    'short_intro' => 'Konut kompleksleri ve rezidans projeleri için pazarlama odaklı profesyonel fotoğrafçılık.',
                    'is_active' => true
                ],
                [
                    'id' => '12',
                    'name' => 'Ofis Fotoğrafçılığı',
                    'slug' => 'ofis-fotografciligi',
                    'short_intro' => 'Kurumsal ofislerin modern ve profesyonel görünümünü fotoğraflarla yansıtıyoruz.',
                    'is_active' => true
                ],
                [
                    'id' => '13',
                    'name' => 'İş Merkezi Fotoğrafçılığı',
                    'slug' => 'is-merkezi-fotografciligi',
                    'short_intro' => 'İş merkezleri ve ticari komplekslerin profesyonel görünümünü fotoğraflarla yansıtıyoruz.',
                    'is_active' => true
                ],
                [
                    'id' => '14',
                    'name' => 'Ticari Alan Fotoğrafçılığı',
                    'slug' => 'ticari-alan-fotografciligi',
                    'short_intro' => 'Mağaza, showroom ve ticari işletmelerin çekici görsellerini profesyonelce üretiyoruz.',
                    'is_active' => true
                ],
                [
                    'id' => '15',
                    'name' => 'Pansiyon Fotoğrafçılığı',
                    'slug' => 'pansiyon-fotografciligi',
                    'short_intro' => 'Pansiyon ve butik konaklama tesislerinin samimi atmosferini fotoğraflarla gösteriyoruz.',
                    'is_active' => true
                ],
                [
                    'id' => '16',
                    'name' => 'Termal Tesis Fotoğrafçılığı',
                    'slug' => 'termal-tesis-fotografciligi',
                    'short_intro' => 'Termal oteller ve spa tesislerinin sağlık ve dinlenme alanlarını profesyonelce çekiyoruz.',
                    'is_active' => true
                ]
            ];

            // Apply filters
            if (isset($params['slug'])) {
                $slug = str_replace('eq.', '', $params['slug']);
                $services = array_filter($services, function ($s) use ($slug) {
                    return $s['slug'] === $slug;
                });
            }

            if (isset($params['is_active'])) {
                $isActive = $params['is_active'] === 'eq.true';
                $services = array_filter($services, function ($s) use ($isActive) {
                    return $s['is_active'] === $isActive;
                });
            }

            return array_values($services);
        }

        private function getMockPortfolio($params = [])
        {
            $projects = [
                [
                    'id' => '1',
                    'title' => 'Modern Villa Projesi - Kaş',
                    'slug' => 'modern-villa-kas',
                    'province_id' => '1',
                    'district_id' => '1',
                    'cover_media_id' => '1',
                    'description' => 'Kaş\'ta deniz manzaralı modern villa projesi için gerçekleştirdiğimiz profesyonel mekan fotoğrafçılığı çalışması.',
                    'year' => 2023,
                    'is_published' => true,
                    'locations_province' => ['name' => 'Antalya', 'slug' => 'antalya'],
                    'locations_district' => ['name' => 'Kaş', 'slug' => 'kas']
                ],
                [
                    'id' => '2',
                    'title' => 'Lüks Otel İç Mekan - Kalkan',
                    'slug' => 'luks-otel-kalkan',
                    'province_id' => '1',
                    'district_id' => '2',
                    'cover_media_id' => '2',
                    'description' => 'Kalkan\'da butik otel projesi için lobby, odalar ve ortak alanların profesyonel fotoğrafçılığı.',
                    'year' => 2023,
                    'is_published' => true,
                    'locations_province' => ['name' => 'Antalya', 'slug' => 'antalya'],
                    'locations_district' => ['name' => 'Kalkan', 'slug' => 'kalkan']
                ],
                [
                    'id' => '3',
                    'title' => 'Butik Otel Projesi - Fethiye',
                    'slug' => 'butik-otel-fethiye',
                    'province_id' => '2',
                    'district_id' => '3',
                    'cover_media_id' => '3',
                    'description' => 'Fethiye\'de yer alan butik otelin tüm alanları için gerçekleştirilen kapsamlı fotoğraf çekimi.',
                    'year' => 2022,
                    'is_published' => true,
                    'locations_province' => ['name' => 'Muğla', 'slug' => 'mugla'],
                    'locations_district' => ['name' => 'Fethiye', 'slug' => 'fethiye']
                ],
                [
                    'id' => '4',
                    'title' => 'Villa Kompleksi - Bodrum',
                    'slug' => 'villa-kompleksi-bodrum',
                    'province_id' => '2',
                    'district_id' => '4',
                    'cover_media_id' => '4',
                    'description' => 'Bodrum\'da lüks villa kompleksi için pazarlama amaçlı profesyonel emlak fotoğrafçılığı.',
                    'year' => 2023,
                    'is_published' => true,
                    'locations_province' => ['name' => 'Muğla', 'slug' => 'mugla'],
                    'locations_district' => ['name' => 'Bodrum', 'slug' => 'bodrum']
                ],
                [
                    'id' => '5',
                    'title' => 'Modern Ofis Tasarımı - İstanbul',
                    'slug' => 'modern-ofis-istanbul',
                    'province_id' => '3',
                    'district_id' => null,
                    'cover_media_id' => '5',
                    'description' => 'İstanbul\'da modern ofis binası için iç mekan ve mimari fotoğrafçılık çalışması.',
                    'year' => 2022,
                    'is_published' => true,
                    'locations_province' => ['name' => 'İstanbul', 'slug' => 'istanbul'],
                    'locations_district' => null
                ],
                [
                    'id' => '6',
                    'title' => 'Restoran İç Mekan - Antalya',
                    'slug' => 'restoran-ic-mekan-antalya',
                    'province_id' => '1',
                    'district_id' => null,
                    'cover_media_id' => '6',
                    'description' => 'Antalya\'da fine dining restoran için ambiyans ve iç mekan fotoğrafçılığı projesi.',
                    'year' => 2023,
                    'is_published' => true,
                    'locations_province' => ['name' => 'Antalya', 'slug' => 'antalya'],
                    'locations_district' => null
                ]
            ];

            // Apply filters
            if (isset($params['is_published'])) {
                $isPublished = $params['is_published'] === 'eq.true';
                $projects = array_filter($projects, function ($p) use ($isPublished) {
                    return $p['is_published'] === $isPublished;
                });
            }

            return array_values($projects);
        }

        private function getMockSeoPages($params = [])
        {
            return [
                [
                    'id' => '1',
                    'type' => 'province',
                    'province_id' => '1',
                    'slug' => 'locations/antalya',
                    'title' => 'Antalya Mekan Fotoğrafçısı | Profesyonel Mimari ve İç Mekan Fotoğrafçılığı',
                    'meta_description' => 'Antalya\'da profesyonel mekan fotoğrafçılığı hizmetleri. Mimari, iç mekan, emlak ve otel fotoğrafçılığı.',
                    'h1' => 'Antalya Mekan Fotoğrafçısı',
                    'content_md' => '## Antalya\'da Profesyonel Mekan Fotoğrafçılığı\n\nAntalya, Türkiye\'nin en önemli turizm merkezlerinden biri...',
                    'published' => true
                ]
            ];
        }
    } // End of SupabaseClient class

} else {
    // Fallback: Create a minimal SupabaseClient if PostgreSQL is not configured
    class SupabaseClient
    {
        public function __construct($url = null, $key = null)
        {
            // Empty constructor for fallback
        }

        public function select($table, $params = [])
        {
            // Return empty array as fallback
            return [];
        }
    }
}

// Global Supabase instance
$supabase = new SupabaseClient();
?>