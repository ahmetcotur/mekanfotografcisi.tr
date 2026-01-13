<?php
/**
 * Admin Password Change API
 */
// Session is now started globally in router.php
require_once __DIR__ . '/middleware.php';
require_once __DIR__ . '/../includes/database.php';

addCorsHeaders();
$user = requireAuth();

$db = new DatabaseClient();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $input = json_decode(file_get_contents('php://input'), true);
    $newPassword = $input['new_password'] ?? '';

    if (empty($newPassword)) {
        echo json_encode(['success' => false, 'error' => 'Password is required']);
        exit;
    }

    if (strlen($newPassword) < 6) {
        echo json_encode(['success' => false, 'error' => 'Password must be at least 6 characters']);
        exit;
    }

    try {
        $passwordHash = password_hash($newPassword, PASSWORD_DEFAULT);

        $result = $db->update('admin_users', [
            'password_hash' => $passwordHash,
            'updated_at' => date('Y-m-d H:i:s')
        ], [
            'id' => $_SESSION['admin_user_id']
        ]);

        echo json_encode([
            'success' => true,
            'password_changed' => true,
            'message' => 'Password updated successfully'
        ]);
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode([
            'success' => false,
            'error' => 'Failed to update password: ' . $e->getMessage()
        ]);
    }
} else {
    http_response_code(405);
    echo json_encode(['success' => false, 'error' => 'Method not allowed']);
}
