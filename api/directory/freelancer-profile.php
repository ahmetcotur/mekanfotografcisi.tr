<?php
/**
 * Public Freelancer Profile API
 * Single approved+public freelancer profile by slug, with resolved portfolio
 * media and published reviews. Powers /fotografcilar/{slug}.
 */

require_once __DIR__ . '/../middleware.php';
require_once __DIR__ . '/../../includes/database.php';

addCorsHeaders();

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    jsonError('Method not allowed', 405);
}

$slug = $_GET['slug'] ?? '';
if (empty($slug)) {
    jsonError('slug is required', 400);
}

$db = new DatabaseClient();

try {
    $rows = $db->select('freelancer_applications', ['slug' => $slug, 'status' => 'approved', 'is_public' => true]);
    if (empty($rows)) {
        jsonError('Photographer not found', 404);
    }
    $profile = $rows[0];

    $portfolioIds = json_decode($profile['portfolio_media_ids'] ?? '[]', true) ?: [];
    $portfolio = [];
    foreach ($portfolioIds as $mediaId) {
        $media = $db->select('media', ['id' => $mediaId]);
        if (!empty($media)) {
            $portfolio[] = $media[0];
        }
    }

    $avatar = null;
    if (!empty($profile['avatar_media_id'])) {
        $avatarRows = $db->select('media', ['id' => $profile['avatar_media_id']]);
        $avatar = $avatarRows[0] ?? null;
    }

    $cover = null;
    if (!empty($profile['cover_media_id'])) {
        $coverRows = $db->select('media', ['id' => $profile['cover_media_id']]);
        $cover = $coverRows[0] ?? null;
    }

    $publicFields = ['id', 'name', 'slug', 'city', 'bio', 'specialization', 'experience', 'rating_avg', 'rating_count'];
    $publicProfile = array_intersect_key($profile, array_flip($publicFields));
    $publicProfile['avatar'] = $avatar;
    $publicProfile['cover'] = $cover;
    $publicProfile['portfolio'] = $portfolio;

    jsonSuccess(['profile' => $publicProfile]);
} catch (Exception $e) {
    error_log('directory/freelancer-profile error: ' . $e->getMessage());
    jsonError('Server error', 500);
}
