<?php
/**
 * Dynamic Sitemap Generator
 * Generates XML sitemap for all published SEO pages
 * mekanfotografcisi.tr
 */

// Load database client
require_once __DIR__ . '/includes/database.php';
require_once __DIR__ . '/includes/helpers.php';

// Set content type to XML
header('Content-Type: application/xml; charset=utf-8');

// Start XML output
echo '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
echo '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . "\n";

// Add homepage
echo "  <url>\n";
echo "    <loc>https://mekanfotografcisi.tr/</loc>\n";
echo "    <lastmod>" . date('Y-m-d') . "</lastmod>\n";
echo "    <changefreq>weekly</changefreq>\n";
echo "    <priority>1.0</priority>\n";
echo "  </url>\n";

// Add main category pages
$mainPages = [
    ['url' => '/services', 'priority' => '0.9'],
    ['url' => '/services/mimari-fotografcilik', 'priority' => '0.8'],
    ['url' => '/services/ic-mekan-fotografciligi', 'priority' => '0.8'],
    ['url' => '/services/emlak-fotografciligi', 'priority' => '0.8'],
    ['url' => '/services/otel-restoran-fotografciligi', 'priority' => '0.8'],
    ['url' => '/locations', 'priority' => '0.9'],
    ['url' => '/locations/antalya', 'priority' => '0.8'],
    ['url' => '/locations/antalya/kas', 'priority' => '0.7'],
    ['url' => '/locations/antalya/kalkan', 'priority' => '0.7'],
    ['url' => '/locations/mugla', 'priority' => '0.8'],
    ['url' => '/locations/mugla/bodrum', 'priority' => '0.7'],
    ['url' => '/locations/mugla/fethiye', 'priority' => '0.7'],
    ['url' => '/portfolio', 'priority' => '0.8'],
    ['url' => '/portfolio/modern-villa-kas', 'priority' => '0.6'],
    ['url' => '/portfolio/luks-otel-kalkan', 'priority' => '0.6'],
    ['url' => '/portfolio/butik-otel-fethiye', 'priority' => '0.6'],
    ['url' => '/portfolio/villa-kompleksi-bodrum', 'priority' => '0.6'],
    ['url' => '/portfolio/modern-ofis-istanbul', 'priority' => '0.6'],
    ['url' => '/portfolio/restoran-ic-mekan-antalya', 'priority' => '0.6']
];

foreach ($mainPages as $page) {
    echo "  <url>\n";
    echo "    <loc>https://mekanfotografcisi.tr{$page['url']}</loc>\n";
    echo "    <lastmod>" . date('Y-m-d') . "</lastmod>\n";
    echo "    <changefreq>weekly</changefreq>\n";
    echo "    <priority>{$page['priority']}</priority>\n";
    echo "  </url>\n";
}

try {
    // Initialize database client
    $db = new DatabaseClient();

    // Get all published SEO pages
    $seoPages = $db->select('seo_pages', [
        'published' => true,
        'select' => 'slug, type, updated_at'
    ]);

    foreach ($seoPages as $page) {
        $lastmod = date('Y-m-d', strtotime($page['updated_at']));

        // Set priority based on page type
        $priority = '0.7';
        $changefreq = 'monthly';

        switch ($page['type']) {
            case 'province':
                $priority = '0.8';
                $changefreq = 'weekly';
                break;
            case 'district':
                $priority = '0.7';
                $changefreq = 'monthly';
                break;
            case 'service':
                $priority = '0.9';
                $changefreq = 'weekly';
                break;
            case 'portfolio':
                $priority = '0.6';
                $changefreq = 'monthly';
                break;
        }

        echo "  <url>\n";
        $loc = 'https://mekanfotografcisi.tr/' . ltrim($page['slug'], '/');
        echo "    <loc>" . e($loc) . "</loc>\n";
        echo "    <lastmod>{$lastmod}</lastmod>\n";
        echo "    <changefreq>{$changefreq}</changefreq>\n";
        echo "    <priority>{$priority}</priority>\n";
        echo "  </url>\n";
    }

} catch (Exception $e) {
    // Log error but continue with basic sitemap
    error_log('Sitemap generation error: ' . $e->getMessage());
}

echo "</urlset>\n";
?>