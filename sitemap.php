<?php
/**
 * Dynamic Sitemap Generator with Google Image Support
 * Generates XML sitemap for all published content with image metadata
 * mekanfotografcisi.tr
 */

// Load database client
require_once __DIR__ . '/includes/database.php';
require_once __DIR__ . '/includes/helpers.php';

// Set content type to XML
header('Content-Type: application/xml; charset=utf-8');

// Start XML output
echo '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
echo '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9" xmlns:image="http://www.google.com/schemas/sitemap-image/1.1">' . "\n";

$urls = [];

/**
 * Helper to add URL to the collection and handle deduplication
 */
function add_url(&$urls, $loc, $lastmod, $changefreq, $priority, $images = [])
{
    // Handle homepage special case
    if (strpos($loc, '/homepage') !== false) {
        $loc = 'https://mekanfotografcisi.tr/';
    }

    // Normalize URL (remove trailing slash except for root)
    $loc = rtrim($loc, '/');
    if ($loc === 'https://mekanfotografcisi.tr')
        $loc .= '/';

    // Keep highest priority if duplicate
    if (!isset($urls[$loc]) || $priority > $urls[$loc]['priority']) {
        $urls[$loc] = [
            'loc' => $loc,
            'lastmod' => $lastmod,
            'changefreq' => $changefreq,
            'priority' => $priority,
            'images' => $images
        ];
    } else if (!empty($images)) {
        // Merge images if they weren't already added
        $urls[$loc]['images'] = array_merge($urls[$loc]['images'], $images);
        // Deduplicate images by loc
        $uniqueImages = [];
        foreach ($urls[$loc]['images'] as $img) {
            $uniqueImages[$img['loc']] = $img;
        }
        $urls[$loc]['images'] = array_values($uniqueImages);
    }
}

/**
 * Fetch images for a specific folder ID
 */
function get_folder_images($db, $folder_id)
{
    if (!$folder_id)
        return [];
    $media = $db->query("SELECT public_url, alt FROM media WHERE folder_id = ? LIMIT 10", [$folder_id]);
    $images = [];
    foreach ($media as $m) {
        $loc = $m['public_url'];
        if (strpos($loc, 'http') !== 0) {
            $loc = 'https://mekanfotografcisi.tr' . (strpos($loc, '/') === 0 ? '' : '/') . $loc;
        }
        $images[] = [
            'loc' => $loc,
            'title' => $m['alt'] ?: 'Mekan Fotoğrafı'
        ];
    }
    return $images;
}

try {
    $db = new DatabaseClient();
    $serviceBase = get_setting('seo_service_base', 'hizmetlerimiz');
    $locationBase = 'hizmet-bolgeleri';

    // 1. Pages from Posts Table (Static Pages & some Services)
    $pages = $db->query("SELECT slug, updated_at, gallery_folder_id FROM posts WHERE post_type = 'page' AND post_status = 'publish'");
    foreach ($pages as $p) {
        $slug = ltrim($p['slug'], '/');
        $loc = 'https://mekanfotografcisi.tr/' . $slug;
        $priority = ($slug === 'homepage' || $slug === '') ? '1.0' : '0.6';
        $freq = ($slug === 'homepage' || $slug === '') ? 'weekly' : 'monthly';

        $images = get_folder_images($db, $p['gallery_folder_id']);
        add_url($urls, $loc, date('Y-m-d', strtotime($p['updated_at'])), $freq, $priority, $images);
    }

    // 2. Active Services from services table
    $activeServices = $db->query("SELECT slug, updated_at, image, gallery_images FROM services WHERE is_active = true OR is_active = 'true'");
    foreach ($activeServices as $s) {
        $loc = 'https://mekanfotografcisi.tr/' . $serviceBase . '/' . ltrim($s['slug'], '/');
        $images = [];
        if (!empty($s['image'])) {
            $imgLoc = $s['image'];
            if (strpos($imgLoc, 'http') !== 0) {
                $imgLoc = 'https://mekanfotografcisi.tr' . (strpos($imgLoc, '/') === 0 ? '' : '/') . $imgLoc;
            }
            $images[] = ['loc' => $imgLoc, 'title' => 'Kapak Fotoğrafı'];
        }
        $gallery = json_decode($s['gallery_images'] ?? '[]', true);
        if (is_array($gallery)) {
            foreach (array_slice($gallery, 0, 9) as $imgUrl) {
                if ($imgUrl) {
                    $imgLoc = $imgUrl;
                    if (strpos($imgLoc, 'http') !== 0) {
                        $imgLoc = 'https://mekanfotografcisi.tr' . (strpos($imgLoc, '/') === 0 ? '' : '/') . $imgLoc;
                    }
                    $images[] = ['loc' => $imgLoc, 'title' => 'Galeri Fotoğrafı'];
                }
            }
        }
        add_url($urls, $loc, date('Y-m-d', strtotime($s['updated_at'])), 'weekly', '0.9', $images);
    }

    // 3. Active Provinces & Districts
    $activeProvinces = $db->query("SELECT id, slug, updated_at FROM locations_province WHERE is_active = true OR is_active = 'true'");
    foreach ($activeProvinces as $p) {
        $pLoc = 'https://mekanfotografcisi.tr/' . $locationBase . '/' . ltrim($p['slug'], '/');
        add_url($urls, $pLoc, date('Y-m-d', strtotime($p['updated_at'])), 'weekly', '0.8');

        // Active Districts
        $activeDistricts = $db->query("SELECT slug, updated_at FROM locations_district WHERE province_id = ? AND (is_active = true OR is_active = 'true')", [$p['id']]);
        foreach ($activeDistricts as $d) {
            $dLoc = 'https://mekanfotografcisi.tr/' . $locationBase . '/' . ltrim($p['slug'], '/') . '/' . ltrim($d['slug'], '/');
            add_url($urls, $dLoc, date('Y-m-d', strtotime($d['updated_at'])), 'monthly', '0.7');
        }
    }

    // 4. SEO Pages (Landing Pages) - Filtered by location active status
    $seoPagesSql = "
        SELECT s.slug, s.updated_at, s.type 
        FROM seo_pages s
        LEFT JOIN locations_province p ON s.province_id = p.id
        LEFT JOIN locations_district d ON s.district_id = d.id
        WHERE s.published = true 
        AND (
            (s.type = 'province' AND (p.is_active = true OR p.is_active = 'true'))
            OR (s.type = 'district' AND (d.is_active = true OR d.is_active = 'true'))
            OR (s.type NOT IN ('province', 'district'))
        )
    ";
    $seoPages = $db->query($seoPagesSql);
    foreach ($seoPages as $page) {
        $slug = ltrim($page['slug'], '/');
        $slug = str_replace('locations/', $locationBase . '/', $slug);
        $slug = str_replace('services/', $serviceBase . '/', $slug);
        $loc = 'https://mekanfotografcisi.tr/' . $slug;

        $priority = '0.7';
        $changefreq = 'monthly';
        switch ($page['type']) {
            case 'province':
                $priority = '0.8';
                $changefreq = 'weekly';
                break;
            case 'service':
                $priority = '0.9';
                $changefreq = 'weekly';
                break;
            case 'district':
                $priority = '0.7';
                break;
        }
        add_url($urls, $loc, date('Y-m-d', strtotime($page['updated_at'])), $changefreq, $priority);
    }

    // Final Output
    foreach ($urls as $u) {
        echo "  <url>\n";
        echo "    <loc>" . e($u['loc']) . "</loc>\n";
        echo "    <lastmod>" . $u['lastmod'] . "</lastmod>\n";
        echo "    <changefreq>" . $u['changefreq'] . "</changefreq>\n";
        echo "    <priority>" . $u['priority'] . "</priority>\n";
        if (!empty($u['images'])) {
            foreach (array_slice($u['images'], 0, 1000) as $img) {
                echo "    <image:image>\n";
                echo "      <image:loc>" . e($img['loc']) . "</image:loc>\n";
                if (!empty($img['title'])) {
                    echo "      <image:title>" . e($img['title']) . "</image:title>\n";
                }
                echo "    </image:image>\n";
            }
        }
        echo "  </url>\n";
    }

} catch (Exception $e) {
    error_log('Sitemap error: ' . $e->getMessage());
}

echo "</urlset>\n";
?>