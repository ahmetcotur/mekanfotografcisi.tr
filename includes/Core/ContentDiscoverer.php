<?php
/**
 * Core Content Discoverer Class
 * Handles on-demand page generation for unknown slugs.
 */

namespace Core;

class ContentDiscoverer
{
    private $db;

    public function __construct($db)
    {
        $this->db = $db;
    }

    /**
     * Try to resolve an unknown slug into a Post object
     */
    public function discover($slug)
    {
        // 1. Check for Service-Location pattern
        $sep = $this->getSetting('seo_service_location_sep', '-');
        $order = $this->getSetting('seo_service_location_order', 'province-service');

        $provinces = $this->db->select('locations_province', ['is_active' => 'true']);
        $services = $this->db->select('services', ['is_active' => 'true']);

        foreach ($provinces as $province) {
            foreach ($services as $service) {
                $expectedSlug = ($order === 'service-province')
                    ? $service['slug'] . $sep . $province['slug']
                    : $province['slug'] . $sep . $service['slug'];

                if ($slug === $expectedSlug) {
                    return $this->generateServiceLocationPage($province, $service, $slug);
                }
            }
        }

        // 2. Check for simple Location pattern: {location}{suffix}
        $suffix = $this->getSetting('seo_location_suffix', '-mekan-fotografcisi');
        $regex = '/^([a-z0-9-]+)' . preg_quote($suffix, '/') . '$/';

        if (preg_match($regex, $slug, $matches)) {
            $locationSlug = $matches[1];

            // A. Town Check
            $town = $this->db->select('locations_town', ['slug' => $locationSlug, 'is_active' => 'true']);
            if (!empty($town)) {
                $district = $this->db->select('locations_district', ['id' => $town[0]['district_id']]);
                return $this->generateTownPage($town[0], $district[0] ?? null, $slug);
            }

            // B. District Check
            $district = $this->db->select('locations_district', ['slug' => $locationSlug, 'is_active' => 'true']);
            if (!empty($district)) {
                $province = $this->db->select('locations_province', ['id' => $district[0]['province_id']]);
                return $this->generateDistrictPage($district[0], $province[0] ?? null, $slug);
            }

            // C. Province Check
            $province = $this->db->select('locations_province', ['slug' => $locationSlug, 'is_active' => 'true']);
            if (!empty($province)) {
                return $this->generateLocationPage($province[0], $slug);
            }
        }

        // 3. Multilevel Location structure: hizmet-bolgeleri/{province}/{district}
        if (preg_match('/^hizmet-bolgeleri\/([a-z0-9-]+)(\/([a-z0-9-]+))?$/', $slug, $matches)) {
            $provSlug = $matches[1];
            $distSlug = $matches[3] ?? null;

            $province = $this->db->select('locations_province', ['slug' => $provSlug, 'is_active' => 'true']);
            if (!empty($province)) {
                if ($distSlug) {
                    $district = $this->db->select('locations_district', [
                        'slug' => $distSlug,
                        'province_id' => $province[0]['id'],
                        'is_active' => 'true'
                    ]);
                    if (!empty($district)) {
                        return $this->generateDistrictPage($district[0], $province[0], $slug);
                    }
                } else {
                    return $this->generateLocationPage($province[0], $slug);
                }
            }
        }

        // 4. Check for Static Template Page: page-{slug}.php
        $staticTemplate = __DIR__ . '/../../templates/hierarchy/page-' . $slug . '.php';
        if (file_exists($staticTemplate)) {
            return $this->generateStaticPage($slug);
        }

        return null;
    }

    /**
     * Generate a post record for a static template page
     */
    private function generateStaticPage($slug)
    {
        $title = ucwords(str_replace('-', ' ', $slug));
        return $this->createPostRecord([
            'title' => $title,
            'slug' => $slug,
            'content' => '',
            'post_type' => 'page',
            'post_status' => 'publish'
        ], []);
    }

    /**
     * Generate and save a Service-Location page
     */
    private function generateServiceLocationPage($province, $service, $slug)
    {
        $template = $this->getSetting('seo_service_location_title_template', '{province} {service}');
        $title = str_replace(['{province}', '{service}'], [$province['name'], $service['name']], $template);
        $content = "{$province['name']} bölgesinde profesyonel {$service['name']} hizmetleri sunuyoruz. Uzman ekibimizle en iyi sonuçları garanti ediyoruz.";

        $globalDescTemplate = $this->getSetting('seo_service_location_meta_desc_template', '{name} bölgesinde profesyonel {service} hizmetleri.');
        $metaDesc = str_replace(['{name}', '{province}', '{service}', '{location}'], [$province['name'], $province['name'], $service['name'], $province['name']], $globalDescTemplate);

        return $this->createPostRecord([
            'title' => $title,
            'slug' => $slug,
            'excerpt' => $metaDesc,
            'content' => $content,
            'post_type' => 'seo_page',
            'post_status' => 'publish'
        ], [
            'province_id' => $province['id'],
            'service_id' => $service['id'],
            'h1' => $title,
            'meta_description' => $metaDesc
        ]);
    }

    /**
     * Generate and save a Town page
     */
    private function generateTownPage($town, $district, $slug)
    {
        $districtName = $district ? $district['name'] : '';
        $template = $this->getSetting('seo_location_title_template', '{name} Mekan Fotoğrafçısı');
        $title = str_replace('{name}', $town['name'], $template);
        $content = "{$town['name']} ({$districtName}) bölgesinde profesyonel otel, villa ve emlak fotoğraf çekimi hizmetleri. Mekanınızın en iyi açılarını yakalıyoruz.";

        $globalDescTemplate = $this->getSetting('seo_location_meta_desc_template', '{name} bölgesinde profesyonel çekim hizmetleri.');
        $metaDesc = str_replace(['{name}', '{location}'], [$town['name'], $town['name']], $globalDescTemplate);

        return $this->createPostRecord([
            'title' => $title,
            'slug' => $slug,
            'excerpt' => $metaDesc,
            'content' => $content,
            'post_type' => 'seo_page',
            'post_status' => 'publish'
        ], [
            'town_id' => $town['id'],
            'district_id' => $district ? $district['id'] : null,
            'h1' => $title,
            'meta_description' => $metaDesc
        ]);
    }

    /**
     * Generate and save a District page
     */
    private function generateDistrictPage($district, $province, $slug)
    {
        $provinceName = $province ? $province['name'] : '';
        $template = $this->getSetting('seo_location_title_template', '{name} Mekan Fotoğrafçısı');
        $title = str_replace('{name}', $district['name'], $template);
        $content = "{$district['name']}, {$provinceName} genelinde profesyonel mekan ve mimari fotoğrafçılık hizmetleri. İşletmeniz için etkileyici görseller.";

        $globalDescTemplate = $this->getSetting('seo_location_meta_desc_template', '{name} bölgesinde profesyonel çekim hizmetleri.');
        $metaDesc = str_replace(['{name}', '{location}'], [$district['name'], $district['name']], $globalDescTemplate);

        return $this->createPostRecord([
            'title' => $title,
            'slug' => $slug,
            'excerpt' => $metaDesc,
            'content' => $content,
            'post_type' => 'seo_page',
            'post_status' => 'publish'
        ], [
            'district_id' => $district['id'],
            'province_id' => $province ? $province['id'] : null,
            'h1' => $title,
            'meta_description' => $metaDesc
        ]);
    }

    /**
     * Generate and save a Location page
     */
    private function generateLocationPage($province, $slug)
    {
        $template = $this->getSetting('seo_location_title_template', '{name} Mekan Fotoğrafçısı');
        $title = str_replace('{name}', $province['name'], $template);
        $content = "{$province['name']} ve çevresinde profesyonel mekan fotoğrafçılığı hizmetleri. Otel, villa, cafe ve restoran çekimlerinde uzman ekibimizle hizmetinizdeyiz.";

        $globalDescTemplate = $this->getSetting('seo_location_meta_desc_template', '{name} bölgesinde profesyonel çekim hizmetleri.');
        $metaDesc = str_replace(['{name}', '{location}'], [$province['name'], $province['name']], $globalDescTemplate);

        return $this->createPostRecord([
            'title' => $title,
            'slug' => $slug,
            'excerpt' => $metaDesc,
            'content' => $content,
            'post_type' => 'seo_page',
            'post_status' => 'publish'
        ], [
            'province_id' => $province['id'],
            'h1' => $title,
            'meta_description' => $metaDesc
        ]);
    }

    /**
     * Get a setting from the database
     */
    private function getSetting($key, $default = '')
    {
        $setting = $this->db->select('settings', ['key' => $key]);
        return (!empty($setting)) ? $setting[0]['value'] : $default;
    }

    /**
     * Create the database record and return a Post object
     */
    private function createPostRecord($postData, $metaData)
    {
        // CRITICAL: Check if slug already exists to prevent Fatal Error (Unique Violation)
        // This can happen if a page was previously created as a draft or by another process.
        $existing = $this->db->select('posts', ['slug' => $postData['slug'], 'limit' => 1]);
        if (!empty($existing)) {
            $post = $existing[0];
        } else {
            $post = $this->db->insert('posts', $postData);
        }

        foreach ($metaData as $key => $value) {
            // Check if meta already exists to avoid duplicates if we found an existing post
            $existingMeta = $this->db->select('post_meta', [
                'post_id' => $post['id'],
                'meta_key' => $key
            ]);

            if (empty($existingMeta)) {
                $this->db->insert('post_meta', [
                    'post_id' => $post['id'],
                    'meta_key' => $key,
                    'meta_value' => json_encode($value)
                ]);
            }
        }

        return new Post($post, $this->db);
    }
}
