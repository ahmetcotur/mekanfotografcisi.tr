<?php
/**
 * Freelancer Claim Quote API
 * Self-service claim of an open marketplace quote. Creates a quote_assignments
 * row with source='self_claim'; the existing UNIQUE(quote_id, freelancer_id)
 * constraint prevents a freelancer from double-claiming the same quote.
 */

require_once __DIR__ . '/../middleware.php';
require_once __DIR__ . '/../../includes/database.php';

addCorsHeaders();
$authUser = requireRole(['freelancer']);

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    jsonError('Method not allowed', 405);
}

$data = json_decode(file_get_contents('php://input'), true);
$quoteId = $data['quote_id'] ?? null;
if (empty($quoteId)) {
    jsonError('quote_id is required', 400);
}

$db = new DatabaseClient();

try {
    $profiles = $db->select('freelancer_applications', ['user_id' => $authUser['user_id']]);
    if (empty($profiles)) {
        jsonError('Freelancer profile not found', 404);
    }
    $profile = $profiles[0];

    if ($profile['status'] !== 'approved') {
        jsonError('Your profile must be approved before claiming requests', 403);
    }

    $quotes = $db->select('quotes', ['id' => $quoteId]);
    if (empty($quotes)) {
        jsonError('Quote not found', 404);
    }
    $quote = $quotes[0];

    if (($quote['visibility'] ?? 'marketplace') !== 'marketplace') {
        jsonError('This request is not open to self-claim', 403);
    }

    // Refuse if the quote is already locked in with someone else.
    $existingAssignments = $db->select('quote_assignments', ['quote_id' => $quoteId]);
    foreach ($existingAssignments as $assignment) {
        if (in_array($assignment['status'], ['accepted', 'completed'], true)) {
            jsonError('This request has already been taken', 409);
        }
    }

    try {
        $assignment = $db->insert('quote_assignments', [
            'quote_id' => $quoteId,
            'freelancer_id' => $profile['id'],
            'status' => 'pending',
            'source' => 'self_claim',
        ]);
    } catch (Exception $e) {
        // UNIQUE(quote_id, freelancer_id) violation - already claimed by this freelancer
        jsonError('You have already claimed this request', 409);
    }

    jsonSuccess(['assignment' => $assignment]);
} catch (Exception $e) {
    error_log('freelancer/claim-quote error: ' . $e->getMessage());
    jsonError('Server error', 500);
}
