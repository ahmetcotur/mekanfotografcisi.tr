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
        // 1. Check for Service-Location pattern: {province}-{service-slug}
        // Example: adana-mimari-fotografcilik

        $provinces = $this->db->select('locations_province', ['is_active' => 'true']);
        foreach ($provinces as $province) {
            $provinceSlug = $province['slug'];
            if (strpos($slug, $provinceSlug . '-') === 0) {
                $serviceSlug = substr($slug, strlen($provinceSlug) + 1);

                // Check if service exists
                $service = $this->db->select('services', ['slug' => $serviceSlug, 'is_active' => 'true']);
                if (!empty($service)) {
                    return $this->generateServiceLocationPage($province, $service[0], $slug);
                }
            }
        }

        // 2. Check for simple Location pattern: {location}-mekan-fotografcisi

        // A. Check Town
        if (preg_match('/^([a-z0-9-]+)-mekan-fotografcisi$/', $slug, $matches)) {
            $locationSlug = $matches[1];

            // Town Check
            $town = $this->db->select('locations_town', ['slug' => $locationSlug, 'is_active' => 'true']);
            if (!empty($town)) {
                // Get Parent Info for better content
                $district = $this->db->select('locations_district', ['id' => $town[0]['district_id']]);
                return $this->generateTownPage($town[0], $district[0] ?? null, $slug);
            }

            // District Check
            $district = $this->db->select('locations_district', ['slug' => $locationSlug, 'is_active' => 'true']);
            if (!empty($district)) {
                $province = $this->db->select('locations_province', ['id' => $district[0]['province_id']]);
                return $this->generateDistrictPage($district[0], $province[0] ?? null, $slug);
            }

            // Province Check (Existing)
            $province = $this->db->select('locations_province', ['slug' => $locationSlug, 'is_active' => 'true']);
            if (!empty($province)) {
                return $this->generateLocationPage($province[0], $slug);
            }
        }

        return null;
    }

    /**
     * Generate and save a Service-Location page
     */
    private function generateServiceLocationPage($province, $service, $slug)
    {
        $title = $province['name'] . ' ' . $service['name'];
        $content = "{$province['name']} bölgesinde profesyonel {$service['name']} hizmetleri sunuyoruz. Uzman ekibimizle en iyi sonuçları garanti ediyoruz.";

        return $this->createPostRecord([
            'title' => $title,
            'slug' => $slug,
            'content' => $content,
            'post_type' => 'seo_page',
            'post_status' => 'publish'
        ], [
            'province_id' => $province['id'],
            'service_id' => $service['id'],
            'h1' => $title,
            'meta_description' => "{$province['name']} {$service['name']} çekimleri için profesyonel çözümler."
        ]);
    }

    /**
     * Generate and save a Town page
     */
    private function generateTownPage($town, $district, $slug)
    {
        $districtName = $district ? $district['name'] : '';
        $title = $town['name'] . ' Mekan Fotoğrafçısı';
        $content = "{$town['name']} ({$districtName}) bölgesinde profesyonel otel, villa ve emlak fotoğraf çekimi hizmetleri. Mekanınızın en iyi açılarını yakalıyoruz.";

        return $this->createPostRecord([
            'title' => $title,
            'slug' => $slug,
            'content' => $content,
            'post_type' => 'seo_page',
            'post_status' => 'publish'
        ], [
            'town_id' => $town['id'],
            'district_id' => $district ? $district['id'] : null,
            'h1' => $title,
            'meta_description' => "{$town['name']} mekan fotoğrafçısı ve mimari çekim hizmetleri."
        ]);
    }

    /**
     * Generate and save a District page
     */
    private function generateDistrictPage($district, $province, $slug)
    {
        $provinceName = $province ? $province['name'] : '';
        $title = $district['name'] . ' Mekan Fotoğrafçısı';
        $content = "{$district['name']}, {$provinceName} genelinde profesyonel mekan ve mimari fotoğrafçılık hizmetleri. İşletmeniz için etkileyici görseller.";

        return $this->createPostRecord([
            'title' => $title,
            'slug' => $slug,
            'content' => $content,
            'post_type' => 'seo_page',
            'post_status' => 'publish'
        ], [
            'district_id' => $district['id'],
            'province_id' => $province ? $province['id'] : null,
            'h1' => $title,
            'meta_description' => "{$district['name']} profesyonel mekan fotoğrafçısı hizmetleri."
        ]);
    }

    /**
     * Generate and save a Location page
     */
    private function generateLocationPage($province, $slug)
    {
        $title = $province['name'] . ' Mekan Fotoğrafçısı';
        $content = "{$province['name']} ve çevresinde profesyonel mekan fotoğrafçılığı hizmetleri. Otel, villa, cafe ve restoran çekimlerinde uzman ekibimizle hizmetinizdeyiz.";

        return $this->createPostRecord([
            'title' => $title,
            'slug' => $slug,
            'content' => $content,
            'post_type' => 'seo_page',
            'post_status' => 'publish'
        ], [
            'province_id' => $province['id'],
            'h1' => $title,
            'meta_description' => "{$province['name']} mekan fotoğrafçısı olarak profesyonel çekim hizmetleri sunmaktayız."
        ]);
    }

    /**
     * Create the database record and return a Post object
     */
    private function createPostRecord($postData, $metaData)
    {
        $post = $this->db->insert('posts', $postData);

        foreach ($metaData as $key => $value) {
            $this->db->insert('post_meta', [
                'post_id' => $post['id'],
                'meta_key' => $key,
                'meta_value' => json_encode($value)
            ]);
        }

        return new Post($post, $this->db);
    }
}
