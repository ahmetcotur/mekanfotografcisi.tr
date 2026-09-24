<?php
/**
 * Public Freelancer Directory API
 * Lists approved + public freelancer profiles, filterable by province/district/
 * specialization. Powers /fotografcilar.
 */

require_once __DIR__ . '/../middleware.php';
require_once __DIR__ . '/../../includes/database.php';

addCorsHeaders();

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    jsonError('Method not allowed', 405);
}

$db = new DatabaseClient();

try {
    $freelancers = $db->select('freelancer_applications', [
        'status' => 'approved',
        'is_public' => true,
        'order' => 'rating_avg DESC, created_at DESC',
    ]);

    $province = $_GET['province'] ?? null;
    $specialization = $_GET['specialization'] ?? null;

    if ($province) {
        $freelancers = array_values(array_filter($freelancers, function ($f) use ($province) {
            if (stripos($f['city'] ?? '', $province) !== false) {
                return true;
            }
            $regions = json_decode($f['working_regions'] ?? '[]', true) ?: [];
            foreach ($regions as $region) {
                if (isset($region['province_name']) && stripos($region['province_name'], $province) !== false) {
                    return true;
                }
            }
            return false;
        }));
    }

    if ($specialization) {
        $freelancers = array_values(array_filter($freelancers, function ($f) use ($specialization) {
            $specs = json_decode($f['specialization'] ?? '[]', true) ?: [];
            return in_array($specialization, $specs, true);
        }));
    }

    // Public listing: strip fields that aren't meant for public consumption.
    $publicFields = ['id', 'name', 'slug', 'city', 'bio', 'specialization', 'experience',
        'avatar_media_id', 'cover_media_id', 'rating_avg', 'rating_count'];
    $result = array_map(function ($f) use ($publicFields) {
        return array_intersect_key($f, array_flip($publicFields));
    }, $freelancers);

    jsonSuccess(['freelancers' => $result, 'total' => count($result)]);
} catch (Exception $e) {
    error_log('directory/freelancers error: ' . $e->getMessage());
    jsonError('Server error', 500);
}
