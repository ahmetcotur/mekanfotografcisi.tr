<?php
// includes/Core/TurkeyLocationService.php

namespace Core;

class TurkeyLocationService
{

    // Turkish Administrative Units API (public and free)
    // We will use a local JSON fallback if API is unavailable
    // Source: https://github.com/ubeydeozdmr/turkiye-api (example structure)

    private $db;

    public function __construct()
    {
        require_once __DIR__ . '/../database.php';
        $this->db = new \DatabaseClient();
    }

    /**
     * Fetch districts for a province name
     */
    public function getDistricts($provinceName)
    {
        $result = $this->db->query("
            SELECT d.* 
            FROM locations_district d
            JOIN locations_province p ON d.province_id = p.id
            WHERE p.name = :name OR p.plate_code = :code
            ORDER BY d.name ASC
        ", [
            'name' => $provinceName,
            'code' => is_numeric($provinceName) ? $provinceName : 'XX'
        ]);

        return array_column($result, 'name');
    }

    /**
     * Fetch neighborhoods/towns for a specific district
     */
    public function getTowns($provinceName, $districtName)
    {
        $result = $this->db->query("
            SELECT t.name 
            FROM locations_town t
            JOIN locations_district d ON t.district_id = d.id
            JOIN locations_province p ON d.province_id = p.id
            WHERE (p.name = :p_name OR p.plate_code = :p_code)
            AND d.name = :d_name
            ORDER BY t.name ASC
        ", [
            'p_name' => $provinceName,
            'p_code' => is_numeric($provinceName) ? $provinceName : 'XX',
            'd_name' => $districtName
        ]);

        return array_column($result, 'name');
    }

    /**
     * Get distance between two provinces in kilometers
     */
    public function getDistance($provinceFrom, $provinceTo)
    {
        $result = $this->db->query("
            SELECT d.distance_km
            FROM locations_city_distance d
            JOIN locations_province p1 ON d.province_from_id = p1.id
            JOIN locations_province p2 ON d.province_to_id = p2.id
            WHERE (p1.name = :from_name OR p1.plate_code = :from_code)
            AND (p2.name = :to_name OR p2.plate_code = :to_code)
        ", [
            'from_name' => $provinceFrom,
            'from_code' => is_numeric($provinceFrom) ? $provinceFrom : 'XX',
            'to_name' => $provinceTo,
            'to_code' => is_numeric($provinceTo) ? $provinceTo : 'XX'
        ]);

        return $result[0]['distance_km'] ?? null;
    }

    private function slugify($text)
    {
        require_once __DIR__ . '/../helpers.php';
        return to_permalink($text);
    }
}
