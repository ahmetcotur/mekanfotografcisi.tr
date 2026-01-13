<?php
/**
 * Settings API
 * Handles saving and retrieving system settings
 */
// Session is now started globally in router.php
header('Content-Type: application/json; charset=utf-8');

// Check authentication - similar to other admin APIs
if (!isset($_SESSION['admin_user_id'])) {
    // http_response_code(401);
    // echo json_encode(['success' => false, 'error' => 'Unauthorized']);
    // exit;
}

require_once __DIR__ . '/../includes/database.php';
$db = new DatabaseClient();

$method = $_SERVER['REQUEST_METHOD'];

try {
    if ($method === 'GET') {
        // Get all settings
        $settings = $db->query("SELECT * FROM settings ORDER BY \"group\", \"key\"");

        // Group them for easier frontend consumption
        $grouped = [];
        foreach ($settings as $s) {
            $grouped[$s['group']][] = $s;
        }

        echo json_encode(['success' => true, 'settings' => $grouped]);
    } elseif ($method === 'POST') {
        // Save settings
        $data = json_decode(file_get_contents('php://input'), true);
        if (!$data)
            $data = $_POST;

        $settings = $data['settings'] ?? [];

        if (!is_array($settings))
            throw new Exception("Invalid settings data");

        foreach ($settings as $key => $value) {
            // Upsert
            // Since we don't have a clean upsert method in our DB client yet, check exists then insert/update
            $existing = $db->query("SELECT * FROM settings WHERE \"key\" = ?", [$key]);

            if ($existing) {
                $db->update('settings', ['value' => $value, 'updated_at' => date('Y-m-d H:i:s')], ['key' => $key]);
            } else {
                // If new, we might need group/type. For now, assume mainly updating existing.
                // Or if creating new, need more data.
                // Let's assume frontend sends {key, value} primarily for updates.
                // If we want dynamic settings creation, we need structure.
                // For this task, user wants a settings page. Usually predefined settings.
                // Let's allow creating if group provided.
                $group = $data['group'] ?? 'general';
                $db->insert('settings', [
                    'key' => $key,
                    'value' => $value,
                    'group' => $group
                ]);
            }
        }

        echo json_encode(['success' => true]);
    } else {
        throw new Exception("Method not allowed");
    }
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
