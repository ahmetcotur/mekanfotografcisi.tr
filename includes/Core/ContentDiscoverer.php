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

        // 2. Check for simple Location pattern: {province}-mekan-fotografcisi
        if (preg_match('/^([a-z0-9-]+)-mekan-fotografcisi$/', $slug, $matches)) {
            $provinceSlug = $matches[1];
            $province = $this->db->select('locations_province', ['slug' => $provinceSlug, 'is_active' => 'true']);
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
