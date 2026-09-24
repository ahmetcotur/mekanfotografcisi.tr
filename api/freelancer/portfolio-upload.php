<?php
/**
 * Freelancer Portfolio Upload API
 * Public-facing sibling of api/admin-upload.php: a logged-in freelancer uploads
 * images into their own portfolio (uploads/media/freelancers/{id}/), and the new
 * media ids are appended to freelancer_applications.portfolio_media_ids.
 */

require_once __DIR__ . '/../middleware.php';
require_once __DIR__ . '/../../includes/database.php';
require_once __DIR__ . '/../../includes/Core/MediaUploader.php';

addCorsHeaders();
$authUser = requireRole(['freelancer']);

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    jsonError('Method not allowed', 405);
}

$db = new DatabaseClient();

try {
    $rows = $db->select('freelancer_applications', ['user_id' => $authUser['user_id']]);
    if (empty($rows)) {
        jsonError('Freelancer profile not found', 404);
    }
    $profile = $rows[0];

    $filesArray = null;
    if (isset($_FILES['files']) && is_array($_FILES['files']['name'])) {
        $filesArray = $_FILES['files'];
    }
    if (!$filesArray) {
        jsonError('No files uploaded', 400);
    }

    $uploader = new \Core\MediaUploader($db);
    $result = $uploader->handleUpload($filesArray, 'freelancers/' . $profile['id']);

    if (!empty($result['uploaded'])) {
        $existingIds = json_decode($profile['portfolio_media_ids'] ?? '[]', true);
        if (!is_array($existingIds)) {
            $existingIds = [];
        }
        foreach ($result['uploaded'] as $file) {
            $existingIds[] = $file['id'];
        }
        $db->update(
            'freelancer_applications',
            ['portfolio_media_ids' => json_encode($existingIds), 'updated_at' => date('Y-m-d H:i:s')],
            ['id' => $profile['id']]
        );
    }

    jsonSuccess([
        'uploaded' => count($result['uploaded']),
        'files' => $result['uploaded'],
        'errors' => $result['errors'],
    ]);
} catch (Exception $e) {
    error_log('freelancer/portfolio-upload error: ' . $e->getMessage());
    jsonError('Server error', 500);
}
