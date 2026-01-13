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

try {
    // Create uploads directory if it doesn't exist
    $uploadDir = __DIR__ . '/../uploads/media/';
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0755, true);
    }
    
    $uploadedFiles = [];
    $errors = [];
    
    // Handle multiple file uploads
    // PHP receives files[] as 'files' key with array structure
    $filesArray = null;
    if (isset($_FILES['files']) && is_array($_FILES['files']['name'])) {
        $filesArray = $_FILES['files'];
    }
    
    if ($filesArray && is_array($filesArray['name'])) {
        $fileCount = count($filesArray['name']);
        
        for ($i = 0; $i < $fileCount; $i++) {
            if ($filesArray['error'][$i] !== UPLOAD_ERR_OK) {
                $errorMsg = 'Upload error';
                switch ($filesArray['error'][$i]) {
                    case UPLOAD_ERR_INI_SIZE:
                    case UPLOAD_ERR_FORM_SIZE:
                        $errorMsg = 'File too large';
                        break;
                    case UPLOAD_ERR_PARTIAL:
                        $errorMsg = 'File partially uploaded';
                        break;
                    case UPLOAD_ERR_NO_FILE:
                        $errorMsg = 'No file uploaded';
                        break;
                    case UPLOAD_ERR_NO_TMP_DIR:
                        $errorMsg = 'Missing temporary folder';
                        break;
                    case UPLOAD_ERR_CANT_WRITE:
                        $errorMsg = 'Failed to write file';
                        break;
                    case UPLOAD_ERR_EXTENSION:
                        $errorMsg = 'Upload blocked by extension';
                        break;
                }
                $errors[] = ($filesArray['name'][$i] ?? 'Unknown') . ': ' . $errorMsg;
                continue;
            }
            
            $file = [
                'name' => $filesArray['name'][$i],
                'type' => $filesArray['type'][$i],
                'tmp_name' => $filesArray['tmp_name'][$i],
                'size' => $filesArray['size'][$i]
            ];
            
            // Validate file size (10MB max)
            if ($file['size'] > 10 * 1024 * 1024) {
                $errors[] = $file['name'] . ': File too large (max 10MB)';
                continue;
            }
            
            // Validate file type
            if (strpos($file['type'], 'image/') !== 0) {
                $errors[] = $file['name'] . ': Invalid file type (images only)';
                continue;
            }
            
            // Generate unique filename
            $timestamp = time();
            $randomStr = bin2hex(random_bytes(8));
            $fileExt = pathinfo($file['name'], PATHINFO_EXTENSION);
            $fileName = $timestamp . '-' . $randomStr . '.' . $fileExt;
            $filePath = $uploadDir . $fileName;
            
            // Move uploaded file
            if (move_uploaded_file($file['tmp_name'], $filePath)) {
                // Get image dimensions
                $imageInfo = getimagesize($filePath);
                $width = $imageInfo[0] ?? null;
                $height = $imageInfo[1] ?? null;
                
                // Create public URL
                $publicUrl = '/uploads/media/' . $fileName;
                $storagePath = 'media/' . $fileName;
                
                // Save to database
                $db = new DatabaseClient();
                $mediaData = [
                    'storage_path' => $storagePath,
                    'public_url' => $publicUrl,
                    'alt' => pathinfo($file['name'], PATHINFO_FILENAME),
                    'width' => $width,
                    'height' => $height,
                    'file_size' => $file['size'],
                    'mime_type' => $file['type']
                ];
                
                $inserted = $db->insert('media', $mediaData);
                
                $uploadedFiles[] = [
                    'id' => $inserted['id'],
                    'name' => $file['name'],
                    'url' => $publicUrl,
                    'size' => $file['size'],
                    'width' => $width,
                    'height' => $height
                ];
            } else {
                $errors[] = $file['name'] . ': Failed to save file';
            }
        }
    } else {
        // Debug info
        $debugInfo = [
            'files_set' => isset($_FILES['files']),
            'files_array_set' => isset($_FILES['files[]']),
            'files_keys' => array_keys($_FILES),
            'post_keys' => array_keys($_POST)
        ];
        error_log('Upload debug: ' . json_encode($debugInfo));
        throw new Exception('No files uploaded. Debug: ' . json_encode($debugInfo));
    }
    
    echo json_encode([
        'success' => true,
        'uploaded' => count($uploadedFiles),
        'files' => $uploadedFiles,
        'errors' => $errors
    ]);
    
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}

