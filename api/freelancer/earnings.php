<?php
/**
 * Freelancer Earnings API
 * Lists the logged-in freelancer's own payments (across all their assignments).
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

    $payments = $db->select('payments', ['freelancer_id' => $profile['id'], 'order' => 'created_at DESC']);

    $totalSucceeded = 0;
    foreach ($payments as $p) {
        if ($p['status'] === 'succeeded') {
            $totalSucceeded += (float) $p['amount'];
        }
    }

    jsonSuccess(['payments' => $payments, 'total_succeeded' => $totalSucceeded]);
} catch (Exception $e) {
    error_log('freelancer/earnings error: ' . $e->getMessage());
    jsonError('Server error', 500);
}
