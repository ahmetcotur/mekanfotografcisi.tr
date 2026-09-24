<?php
/**
 * Freelancer Profile API
 * A logged-in freelancer reads/updates their own freelancer_applications row.
 * Ownership is enforced via user_id (never trusts a client-supplied id).
 */

require_once __DIR__ . '/../middleware.php';
require_once __DIR__ . '/../../includes/database.php';
require_once __DIR__ . '/../../includes/helpers.php';

addCorsHeaders();
$authUser = requireRole(['freelancer']);

$db = new DatabaseClient();
$method = $_SERVER['REQUEST_METHOD'];

try {
    $rows = $db->select('freelancer_applications', ['user_id' => $authUser['user_id']]);
    if (empty($rows)) {
        jsonError('Freelancer profile not found', 404);
    }
    $profile = $rows[0];

    if ($method === 'GET') {
        jsonSuccess(['profile' => $profile]);
    } elseif ($method === 'PUT') {
        $data = json_decode(file_get_contents('php://input'), true);
        if (!is_array($data)) {
            jsonError('Invalid JSON body', 400);
        }

        $updatable = [];

        if (array_key_exists('bio', $data)) {
            $updatable['bio'] = sanitizeString($data['bio']);
        }
        if (array_key_exists('city', $data)) {
            $updatable['city'] = sanitizeString($data['city']);
        }
        if (array_key_exists('phone', $data)) {
            $updatable['phone'] = sanitizeString($data['phone']);
        }
        if (array_key_exists('portfolio_url', $data)) {
            $updatable['portfolio_url'] = $data['portfolio_url'] !== null ? trim($data['portfolio_url']) : null;
        }
        if (array_key_exists('specialization', $data)) {
            if (!is_array($data['specialization']) || count($data['specialization']) === 0) {
                jsonError('specialization must be a non-empty array', 400);
            }
            $updatable['specialization'] = json_encode($data['specialization']);
        }
        if (array_key_exists('working_regions', $data)) {
            if (!is_array($data['working_regions'])) {
                jsonError('working_regions must be an array', 400);
            }
            $updatable['working_regions'] = json_encode($data['working_regions']);
        }
        if (array_key_exists('avatar_media_id', $data)) {
            $updatable['avatar_media_id'] = $data['avatar_media_id'] ?: null;
        }
        if (array_key_exists('cover_media_id', $data)) {
            $updatable['cover_media_id'] = $data['cover_media_id'] ?: null;
        }
        if (array_key_exists('is_public', $data)) {
            // A freelancer may only go public once admin has approved the
            // application; they can always take themselves private again.
            if ($data['is_public'] && $profile['status'] !== 'approved') {
                jsonError('Profile must be approved by an admin before it can be made public', 403);
            }
            $updatable['is_public'] = (bool) $data['is_public'];
        }

        if (empty($updatable)) {
            jsonError('No updatable fields provided', 400);
        }

        $updatable['updated_at'] = date('Y-m-d H:i:s');
        $updated = $db->update('freelancer_applications', $updatable, ['id' => $profile['id']]);

        jsonSuccess(['profile' => $updated[0] ?? null]);
    } else {
        jsonError('Method not allowed', 405);
    }
} catch (Exception $e) {
    error_log('freelancer/profile error: ' . $e->getMessage());
    jsonError('Server error', 500);
}
