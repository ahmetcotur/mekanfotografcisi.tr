<?php
/**
 * Media Uploader Service
 * Shared image-upload handling (validation, storage, media table insert) used by
 * both the admin panel upload endpoint and the freelancer portfolio uploader.
 */

namespace Core;

use DatabaseClient;

class MediaUploader
{
    private $db;

    public function __construct(DatabaseClient $db)
    {
        $this->db = $db;
    }

    /**
     * Handle a batch of uploaded files from $_FILES['files'].
     *
     * @param array $filesArray $_FILES['files'] (must already be the multi-file array form)
     * @param string $subdir Subdirectory under uploads/media/ to store files in (e.g. 'freelancers/42')
     * @param array $extra Extra columns to merge into every inserted media row (e.g. ['folder_id' => ...])
     * @return array ['uploaded' => [...], 'errors' => [...]]
     */
    public function handleUpload($filesArray, $subdir = '', array $extra = [])
    {
        $uploaded = [];
        $errors = [];

        if (!$filesArray || !is_array($filesArray['name'] ?? null)) {
            return ['uploaded' => $uploaded, 'errors' => ['No files uploaded']];
        }

        $subdir = trim($subdir, '/');
        $uploadDir = __DIR__ . '/../../uploads/media/' . ($subdir !== '' ? $subdir . '/' : '');
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }

        $fileCount = count($filesArray['name']);

        for ($i = 0; $i < $fileCount; $i++) {
            if ($filesArray['error'][$i] !== UPLOAD_ERR_OK) {
                $errors[] = ($filesArray['name'][$i] ?? 'Unknown') . ': ' . $this->uploadErrorMessage($filesArray['error'][$i]);
                continue;
            }

            $file = [
                'name' => $filesArray['name'][$i],
                'type' => $filesArray['type'][$i],
                'tmp_name' => $filesArray['tmp_name'][$i],
                'size' => $filesArray['size'][$i],
            ];

            if ($file['size'] > 10 * 1024 * 1024) {
                $errors[] = $file['name'] . ': File too large (max 10MB)';
                continue;
            }

            if (strpos($file['type'], 'image/') !== 0) {
                $errors[] = $file['name'] . ': Invalid file type (images only)';
                continue;
            }

            $timestamp = time();
            $randomStr = bin2hex(random_bytes(8));
            $fileExt = pathinfo($file['name'], PATHINFO_EXTENSION);
            $fileName = $timestamp . '-' . $randomStr . '.' . $fileExt;
            $filePath = $uploadDir . $fileName;

            if (!move_uploaded_file($file['tmp_name'], $filePath)) {
                $errors[] = $file['name'] . ': Failed to save file';
                continue;
            }

            $imageInfo = getimagesize($filePath);
            $width = $imageInfo[0] ?? null;
            $height = $imageInfo[1] ?? null;

            $publicUrl = '/uploads/media/' . ($subdir !== '' ? $subdir . '/' : '') . $fileName;
            $storagePath = 'media/' . ($subdir !== '' ? $subdir . '/' : '') . $fileName;

            $mediaData = array_merge([
                'storage_path' => $storagePath,
                'public_url' => $publicUrl,
                'alt' => pathinfo($file['name'], PATHINFO_FILENAME),
                'width' => $width,
                'height' => $height,
                'file_size' => $file['size'],
                'mime_type' => $file['type'],
            ], $extra);

            $inserted = $this->db->insert('media', $mediaData);

            $uploaded[] = [
                'id' => $inserted['id'],
                'name' => $file['name'],
                'url' => $publicUrl,
                'size' => $file['size'],
                'width' => $width,
                'height' => $height,
            ];
        }

        return ['uploaded' => $uploaded, 'errors' => $errors];
    }

    private function uploadErrorMessage($code)
    {
        switch ($code) {
            case UPLOAD_ERR_INI_SIZE:
            case UPLOAD_ERR_FORM_SIZE:
                return 'File too large';
            case UPLOAD_ERR_PARTIAL:
                return 'File partially uploaded';
            case UPLOAD_ERR_NO_FILE:
                return 'No file uploaded';
            case UPLOAD_ERR_NO_TMP_DIR:
                return 'Missing temporary folder';
            case UPLOAD_ERR_CANT_WRITE:
                return 'Failed to write file';
            case UPLOAD_ERR_EXTENSION:
                return 'Upload blocked by extension';
            default:
                return 'Upload error';
        }
    }
}
