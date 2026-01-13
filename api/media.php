<?php
/**
 * Media Management API
 */
require_once __DIR__ . '/middleware.php';
require_once __DIR__ . '/../includes/database.php';

addCorsHeaders();
$user = requireAuth();

$db = new DatabaseClient();
$uploadDir = __DIR__ . '/../uploads/';

if (!is_dir($uploadDir)) {
    mkdir($uploadDir, 0777, true);
}

try {
    $action = $_GET['action'] ?? $_POST['action'] ?? 'list';

    if ($action === 'list') {
        $folderId = !empty($_GET['folder_id']) ? $_GET['folder_id'] : null;

        $folders = $db->select('media_folders', ['parent_id' => $folderId]);
        $files = $db->select('media', ['folder_id' => $folderId]);

        // If we have a folderId, get parent_id for breadcrumb
        $parentFolderId = null;
        if ($folderId) {
            $current = $db->select('media_folders', ['id' => $folderId]);
            $parentFolderId = !empty($current) ? $current[0]['parent_id'] : null;
        }

        echo json_encode([
            'success' => true,
            'folders' => $folders ?: [],
            'files' => $files ?: [],
            'parent_id' => $parentFolderId
        ]);

    } elseif ($action === 'create-folder') {
        $input = json_decode(file_get_contents('php://input'), true);
        $name = $input['name'] ?? '';
        $parentId = !empty($input['parent_id']) ? $input['parent_id'] : null;

        if (empty($name))
            throw new Exception('Folder name is required');

        $db->insert('media_folders', [
            'id' => sprintf('%04x%04x-%04x-%04x-%04x-%04x%04x%04x', mt_rand(0, 0xffff), mt_rand(0, 0xffff), mt_rand(0, 0xffff), mt_rand(0, 0x0fff) | 0x4000, mt_rand(0, 0x3fff) | 0x8000, mt_rand(0, 0xffff), mt_rand(0, 0xffff), mt_rand(0, 0xffff)),
            'name' => $name,
            'parent_id' => $parentId
        ]);

        echo json_encode(['success' => true]);

    } elseif ($action === 'upload') {
        if (!isset($_FILES['file']))
            throw new Exception('No file uploaded');

        $folderId = !empty($_POST['folder_id']) ? $_POST['folder_id'] : null;
        $file = $_FILES['file'];
        $fileName = time() . '_' . preg_replace('/[^a-z0-9\._-]/i', '', $file['name']);
        $targetPath = $uploadDir . $fileName;

        if (move_uploaded_file($file['tmp_name'], $targetPath)) {
            $publicUrl = '/uploads/' . $fileName;

            // Get image info
            $size = getimagesize($targetPath);
            $width = $size[0] ?? null;
            $height = $size[1] ?? null;
            $fileSize = filesize($targetPath);
            $mimeType = $file['type'];

            $result = $db->insert('media', [
                'id' => sprintf('%04x%04x-%04x-%04x-%04x-%04x%04x%04x', mt_rand(0, 0xffff), mt_rand(0, 0xffff), mt_rand(0, 0xffff), mt_rand(0, 0x0fff) | 0x4000, mt_rand(0, 0x3fff) | 0x8000, mt_rand(0, 0xffff), mt_rand(0, 0xffff), mt_rand(0, 0xffff)),
                'storage_path' => $targetPath,
                'public_url' => $publicUrl,
                'alt' => pathinfo($file['name'], PATHINFO_FILENAME),
                'width' => $width,
                'height' => $height,
                'file_size' => $fileSize,
                'mime_type' => $mimeType,
                'folder_id' => $folderId
            ]);

            echo json_encode(['success' => true, 'data' => $result]);
        } else {
            throw new Exception('Failed to move uploaded file');
        }

    } elseif ($action === 'delete-file') {
        $input = json_decode(file_get_contents('php://input'), true);
        $id = $input['id'] ?? '';

        $file = $db->select('media', ['id' => $id]);
        if (!empty($file)) {
            if (file_exists($file[0]['storage_path'])) {
                unlink($file[0]['storage_path']);
            }
            $db->delete('media', ['id' => $id]);
        }
        echo json_encode(['success' => true]);

    } elseif ($action === 'delete-folder') {
        $input = json_decode(file_get_contents('php://input'), true);
        $id = $input['id'] ?? '';

        // Safety: only delete empty folders?
        $files = $db->select('media', ['folder_id' => $id]);
        $subfolders = $db->select('media_folders', ['parent_id' => $id]);

        if (!empty($files) || !empty($subfolders)) {
            throw new Exception('Folder is not empty');
        }

        $db->delete('media_folders', ['id' => $id]);
        echo json_encode(['success' => true]);
    }

} catch (Exception $e) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
