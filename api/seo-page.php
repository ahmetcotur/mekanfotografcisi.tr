<?php
/**
 * SEO Page API Handler
 * Serves dynamically generated SEO pages for locations and services
 * mekanfotografcisi.tr
 */

// Load configuration
require_once __DIR__ . '/../includes/config.php';

// CORS headers
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET");
header("Access-Control-Allow-Headers: Content-Type");
header("Content-Type: application/json; charset=utf-8");

// Supabase configuration
$SUPABASE_URL = env('SUPABASE_URL', 'YOUR_SUPABASE_URL');
$SUPABASE_ANON_KEY = env('SUPABASE_ANON_KEY', 'YOUR_SUPABASE_ANON_KEY');

// Only handle GET requests
if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);
    exit;
}

// Load helpers
require_once __DIR__ . '/../includes/helpers.php';

// Get the requested slug from URL parameter
$slug = sanitizeSlug($_GET['slug'] ?? '');

if (empty($slug)) {
    http_response_code(400);
    echo json_encode(['error' => 'Slug parameter is required']);
    exit;
}

// Function to make Supabase API request
function supabaseRequest($endpoint, $params = []) {
    global $SUPABASE_URL, $SUPABASE_ANON_KEY;
    
    $url = $SUPABASE_URL . '/rest/v1/' . $endpoint;
    if (!empty($params)) {
        $url .= '?' . http_build_query($params);
    }
    
    $context = stream_context_create([
        'http' => [
            'method' => 'GET',
            'header' => [
                'apikey: ' . $SUPABASE_ANON_KEY,
                'Authorization: Bearer ' . $SUPABASE_ANON_KEY,
                'Content-Type: application/json'
            ]
        ]
    ]);
    
    $response = file_get_contents($url, false, $context);
    
    if ($response === false) {
        throw new Exception('Failed to fetch data from Supabase');
    }
    
    return json_decode($response, true);
}

try {
    // Query SEO page by slug
    $seoPages = supabaseRequest('seo_pages', [
        'slug' => 'eq.' . $slug,
        'published' => 'eq.true',
        'select' => '*'
    ]);
    
    if (empty($seoPages)) {
        http_response_code(404);
        echo json_encode(['error' => 'Page not found']);
        exit;
    }
    
    $seoPage = $seoPages[0];
    
    // Get related data based on page type
    $relatedData = [];
    
    switch ($seoPage['type']) {
        case 'province':
            if ($seoPage['province_id']) {
                $provinces = supabaseRequest('locations_province', [
                    'id' => 'eq.' . $seoPage['province_id'],
                    'select' => '*'
                ]);
                $relatedData['province'] = $provinces[0] ?? null;
                
                // Get active districts in this province
                $districts = supabaseRequest('locations_district', [
                    'province_id' => 'eq.' . $seoPage['province_id'],
                    'is_active' => 'eq.true',
                    'select' => 'id,name,slug'
                ]);
                $relatedData['districts'] = $districts;
            }
            break;
            
        case 'district':
            if ($seoPage['district_id']) {
                $districts = supabaseRequest('locations_district', [
                    'id' => 'eq.' . $seoPage['district_id'],
                    'select' => '*,locations_province(*)'
                ]);
                $relatedData['district'] = $districts[0] ?? null;
            }
            break;
            
        case 'service':
            if ($seoPage['service_id']) {
                $services = supabaseRequest('services', [
                    'id' => 'eq.' . $seoPage['service_id'],
                    'select' => '*'
                ]);
                $relatedData['service'] = $services[0] ?? null;
            }
            break;
            
        case 'portfolio':
            if ($seoPage['portfolio_id']) {
                $portfolios = supabaseRequest('portfolio_projects', [
                    'id' => 'eq.' . $seoPage['portfolio_id'],
                    'select' => '*,locations_province(*),locations_district(*)'
                ]);
                $relatedData['portfolio'] = $portfolios[0] ?? null;
            }
            break;
    }
    
    // Return the SEO page data with related information
    $response = [
        'success' => true,
        'data' => [
            'seo_page' => $seoPage,
            'related' => $relatedData,
            'breadcrumbs' => generateBreadcrumbs($seoPage, $relatedData),
            'canonical_url' => 'https://mekanfotografcisi.tr/' . $slug,
            'schema_markup' => generateSchemaMarkup($seoPage, $relatedData)
        ]
    ];
    
    echo json_encode($response, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Internal server error: ' . $e->getMessage()]);
}

/**
 * Generate breadcrumbs for the page
 */
function generateBreadcrumbs($seoPage, $relatedData) {
    $breadcrumbs = [
        ['name' => 'Ana Sayfa', 'url' => '/']
    ];
    
    switch ($seoPage['type']) {
        case 'province':
            $breadcrumbs[] = ['name' => 'Lokasyonlar', 'url' => '/locations'];
            if (isset($relatedData['province'])) {
                $breadcrumbs[] = [
                    'name' => $relatedData['province']['name'],
                    'url' => '/locations/' . $relatedData['province']['slug']
                ];
            }
            break;
            
        case 'district':
            $breadcrumbs[] = ['name' => 'Lokasyonlar', 'url' => '/locations'];
            if (isset($relatedData['district']['locations_province'])) {
                $province = $relatedData['district']['locations_province'];
                $breadcrumbs[] = [
                    'name' => $province['name'],
                    'url' => '/locations/' . $province['slug']
                ];
            }
            if (isset($relatedData['district'])) {
                $breadcrumbs[] = [
                    'name' => $relatedData['district']['name'],
                    'url' => '/locations/' . $relatedData['district']['locations_province']['slug'] . '/' . $relatedData['district']['slug']
                ];
            }
            break;
            
        case 'service':
            $breadcrumbs[] = ['name' => 'Hizmetler', 'url' => '/services'];
            if (isset($relatedData['service'])) {
                $breadcrumbs[] = [
                    'name' => $relatedData['service']['name'],
                    'url' => '/services/' . $relatedData['service']['slug']
                ];
            }
            break;
            
        case 'portfolio':
            $breadcrumbs[] = ['name' => 'Portfolyo', 'url' => '/portfolio'];
            if (isset($relatedData['portfolio'])) {
                $breadcrumbs[] = [
                    'name' => $relatedData['portfolio']['title'],
                    'url' => '/portfolio/' . $relatedData['portfolio']['slug']
                ];
            }
            break;
    }
    
    return $breadcrumbs;
}

/**
 * Generate JSON-LD schema markup
 */
function generateSchemaMarkup($seoPage, $relatedData) {
    $baseSchema = [
        '@context' => 'https://schema.org',
        '@type' => 'LocalBusiness',
        'name' => 'Mekan Fotoğrafçısı',
        'description' => $seoPage['meta_description'],
        'url' => 'https://mekanfotografcisi.tr/' . $seoPage['slug'],
        'telephone' => '+90 507 467 75 02',
        'email' => 'info@mekanfotografcisi.tr',
        'address' => [
            '@type' => 'PostalAddress',
            'streetAddress' => 'Kalkan Mah. Şehitler Cad. no 7',
            'addressLocality' => 'Kaş',
            'addressRegion' => 'Antalya',
            'addressCountry' => 'TR'
        ],
        'geo' => [
            '@type' => 'GeoCoordinates',
            'latitude' => 36.2021,
            'longitude' => 29.6384
        ],
        'serviceArea' => [
            '@type' => 'GeoCircle',
            'geoMidpoint' => [
                '@type' => 'GeoCoordinates',
                'latitude' => 36.5,
                'longitude' => 29.0
            ],
            'geoRadius' => '200000'
        ]
    ];
    
    // Add specific schema based on page type
    switch ($seoPage['type']) {
        case 'province':
        case 'district':
            $baseSchema['@type'] = 'ProfessionalService';
            $baseSchema['serviceType'] = 'Mekan Fotoğrafçılığı';
            if (isset($relatedData['province'])) {
                $baseSchema['areaServed'] = [
                    '@type' => 'State',
                    'name' => $relatedData['province']['name']
                ];
            }
            break;
            
        case 'service':
            $baseSchema['@type'] = 'ProfessionalService';
            if (isset($relatedData['service'])) {
                $baseSchema['serviceType'] = $relatedData['service']['name'];
            }
            break;
            
        case 'portfolio':
            $baseSchema['@type'] = 'CreativeWork';
            if (isset($relatedData['portfolio'])) {
                $baseSchema['name'] = $relatedData['portfolio']['title'];
                $baseSchema['creator'] = [
                    '@type' => 'Organization',
                    'name' => 'Mekan Fotoğrafçısı'
                ];
            }
            break;
    }
    
    return $baseSchema;
}
?>