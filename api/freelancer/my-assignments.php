<?php
/**
 * Freelancer My Assignments API
 * Lists the logged-in freelancer's own quote_assignments (claimed or admin-assigned),
 * enriched with the related quote. Powers the "İşlerim" panel page.
 */

require_once __DIR__ . '/../middleware.php';
require_once __DIR__ . '/../../includes/database.php';

addCorsHeaders();
$authUser = requireRole(['freelancer']);

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    jsonError('Method not allowed', 405);
}

$db = new DatabaseClient();

try {
    $profiles = $db->select('freelancer_applications', ['user_id' => $authUser['user_id']]);
    if (empty($profiles)) {
        jsonError('Freelancer profile not found', 404);
    }
    $profile = $profiles[0];

    $assignments = $db->select('quote_assignments', ['freelancer_id' => $profile['id']]);
    foreach ($assignments as &$assignment) {
        $quote = $db->select('quotes', ['id' => $assignment['quote_id']]);
        $assignment['quote'] = $quote[0] ?? null;
    }

    jsonSuccess(['assignments' => $assignments]);
} catch (Exception $e) {
    error_log('freelancer/my-assignments error: ' . $e->getMessage());
    jsonError('Server error', 500);
}
