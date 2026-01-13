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

// Add dynamic content
try {
    $db = new DatabaseClient();

    // 1. Published SEO Pages (Slug-based)
    $seoPages = $db->select('seo_pages', [
        'published' => true,
        'select' => 'slug, type, updated_at'
    ]);

    foreach ($seoPages as $page) {
        $lastmod = date('Y-m-d', strtotime($page['updated_at']));
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

    // 2. Active Services
    $activeServices = $db->query("SELECT slug, updated_at FROM services WHERE is_active = true");
    foreach ($activeServices as $s) {
        echo "  <url>\n";
        echo "    <loc>https://mekanfotografcisi.tr/services/" . e($s['slug']) . "</loc>\n";
        echo "    <lastmod>" . date('Y-m-d', strtotime($s['updated_at'])) . "</lastmod>\n";
        echo "    <changefreq>weekly</changefreq>\n";
        echo "    <priority>0.8</priority>\n";
        echo "  </url>\n";
    }

    // 3. Active Provinces & Districts
    $activeProvinces = $db->query("SELECT id, slug, updated_at FROM locations_province WHERE is_active = true");
    foreach ($activeProvinces as $p) {
        echo "  <url>\n";
        echo "    <loc>https://mekanfotografcisi.tr/locations/" . e($p['slug']) . "</loc>\n";
        echo "    <lastmod>" . date('Y-m-d', strtotime($p['updated_at'])) . "</lastmod>\n";
        echo "    <changefreq>weekly</changefreq>\n";
        echo "    <priority>0.8</priority>\n";
        echo "  </url>\n";

        // Active Districts for this Province
        $activeDistricts = $db->query("SELECT slug, updated_at FROM locations_district WHERE province_id = ? AND is_active = true", [$p['id']]);
        foreach ($activeDistricts as $d) {
            echo "  <url>\n";
            echo "    <loc>https://mekanfotografcisi.tr/locations/" . e($p['slug']) . "/" . e($d['slug']) . "</loc>\n";
            echo "    <lastmod>" . date('Y-m-d', strtotime($d['updated_at'])) . "</lastmod>\n";
            echo "    <changefreq>monthly</changefreq>\n";
            echo "    <priority>0.7</priority>\n";
            echo "  </url>\n";
        }
    }

} catch (Exception $e) {
    error_log('Sitemap error: ' . $e->getMessage());
}


echo "</urlset>\n";
?>