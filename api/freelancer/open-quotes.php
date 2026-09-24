<?php
/**
 * Freelancer Open Quotes API
 * Lists open marketplace shooting requests ranked for the logged-in freelancer,
 * via FreelancerMatcher::findMatchingQuotesForFreelancer().
 */

require_once __DIR__ . '/../middleware.php';
require_once __DIR__ . '/../../includes/database.php';
require_once __DIR__ . '/../../includes/Core/FreelancerMatcher.php';

addCorsHeaders();
$authUser = requireRole(['freelancer']);

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    jsonError('Method not allowed', 405);
}

$db = new DatabaseClient();

try {
    $rows = $db->select('freelancer_applications', ['user_id' => $authUser['user_id']]);
    if (empty($rows)) {
        jsonError('Freelancer profile not found', 404);
    }
    $profile = $rows[0];

    if ($profile['status'] !== 'approved') {
        jsonSuccess(['matches' => [], 'total' => 0, 'note' => 'Profile not yet approved']);
    }

    $matcher = new \Core\FreelancerMatcher($db);
    $matches = $matcher->findMatchingQuotesForFreelancer($profile['id']);

    jsonSuccess(['matches' => $matches, 'total' => count($matches)]);
} catch (Exception $e) {
    error_log('freelancer/open-quotes error: ' . $e->getMessage());
    jsonError('Server error', 500);
}
