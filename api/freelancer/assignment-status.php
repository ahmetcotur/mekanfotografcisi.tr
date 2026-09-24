<?php
/**
 * Freelancer Assignment Status API
 * A freelancer accepts/rejects/completes their own quote_assignments row.
 * Ownership is verified before any write (freelancer_id must match the caller).
 */

require_once __DIR__ . '/../middleware.php';
require_once __DIR__ . '/../../includes/database.php';
require_once __DIR__ . '/../../includes/helpers.php';

addCorsHeaders();
$authUser = requireRole(['freelancer']);

if ($_SERVER['REQUEST_METHOD'] !== 'PUT') {
    jsonError('Method not allowed', 405);
}

$data = json_decode(file_get_contents('php://input'), true);
$assignmentId = $data['id'] ?? null;
$status = $data['status'] ?? null;

$allowedStatuses = ['accepted', 'rejected', 'completed'];
if (empty($assignmentId) || !in_array($status, $allowedStatuses, true)) {
    jsonError('id and a valid status (accepted|rejected|completed) are required', 400);
}

$db = new DatabaseClient();

try {
    $profiles = $db->select('freelancer_applications', ['user_id' => $authUser['user_id']]);
    if (empty($profiles)) {
        jsonError('Freelancer profile not found', 404);
    }
    $profile = $profiles[0];

    $assignments = $db->select('quote_assignments', ['id' => $assignmentId]);
    if (empty($assignments)) {
        jsonError('Assignment not found', 404);
    }
    $assignment = $assignments[0];

    if ((int) $assignment['freelancer_id'] !== (int) $profile['id']) {
        jsonError('Forbidden', 403);
    }

    $updateData = [
        'status' => $status,
        'updated_at' => date('Y-m-d H:i:s'),
    ];
    if (array_key_exists('freelancer_note', $data)) {
        $updateData['freelancer_note'] = sanitizeString($data['freelancer_note']);
    }

    $updated = $db->update('quote_assignments', $updateData, ['id' => $assignmentId]);

    jsonSuccess(['assignment' => $updated[0] ?? null]);
} catch (Exception $e) {
    error_log('freelancer/assignment-status error: ' . $e->getMessage());
    jsonError('Server error', 500);
}
