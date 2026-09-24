<?php
/**
 * Admin Upload API
 * Handles file uploads for admin panel
 */

session_start();
header('Content-Type: application/json; charset=utf-8');

// Check authentication
if (!isset($_SESSION['admin_user_id'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'error' => 'Unauthorized']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'error' => 'Method not allowed']);
    exit;
}

require_once __DIR__ . '/../includes/database.php';
require_once __DIR__ . '/../includes/Core/MediaUploader.php';

try {
    $filesArray = null;
    if (isset($_FILES['files']) && is_array($_FILES['files']['name'])) {
        $filesArray = $_FILES['files'];
    }

    if (!$filesArray) {
        $debugInfo = [
            'files_set' => isset($_FILES['files']),
            'files_array_set' => isset($_FILES['files[]']),
            'files_keys' => array_keys($_FILES),
            'post_keys' => array_keys($_POST)
        ];
        error_log('Upload debug: ' . json_encode($debugInfo));
        throw new Exception('No files uploaded. Debug: ' . json_encode($debugInfo));
    }

    $db = new DatabaseClient();
    $uploader = new \Core\MediaUploader($db);
    $result = $uploader->handleUpload($filesArray);

    echo json_encode([
        'success' => true,
        'uploaded' => count($result['uploaded']),
        'files' => $result['uploaded'],
        'errors' => $result['errors']
    ]);

} catch (Exception $e) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}

