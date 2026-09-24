<?php
/**
 * Client My Quotes API
 * Lists the logged-in client's own quotes (quotes.user_id), enriched with any
 * assignment/freelancer info so the client can see who's handling their request.
 */

require_once __DIR__ . '/../middleware.php';
require_once __DIR__ . '/../../includes/database.php';

addCorsHeaders();
$authUser = requireRole(['client']);

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    jsonError('Method not allowed', 405);
}

$db = new DatabaseClient();

try {
    $quotes = $db->select('quotes', ['user_id' => $authUser['user_id'], 'order' => 'created_at DESC']);

    foreach ($quotes as &$quote) {
        $assignments = $db->select('quote_assignments', ['quote_id' => $quote['id']]);
        foreach ($assignments as &$assignment) {
            $freelancer = $db->select('freelancer_applications', ['id' => $assignment['freelancer_id']]);
            $assignment['freelancer'] = !empty($freelancer)
                ? ['name' => $freelancer[0]['name'], 'phone' => $freelancer[0]['phone'], 'slug' => $freelancer[0]['slug']]
                : null;
        }
        $quote['assignments'] = $assignments;
    }

    jsonSuccess(['quotes' => $quotes]);
} catch (Exception $e) {
    error_log('client/my-quotes error: ' . $e->getMessage());
    jsonError('Server error', 500);
}
