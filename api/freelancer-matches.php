<?php
/**
 * Freelancer Matching API
 * Returns ranked list of freelancers for a given quote
 */

require_once __DIR__ . '/middleware.php';
require_once __DIR__ . '/../includes/database.php';
require_once __DIR__ . '/../includes/Core/FreelancerMatcher.php';

addCorsHeaders();
$user = requireAuth();

$db = new DatabaseClient();

try {
    if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
        jsonError('Method not allowed', 405);
    }

    $quoteId = $_GET['quote_id'] ?? null;

    if (!$quoteId) {
        jsonError('quote_id is required', 400);
    }

    // Get quote details
    $quotes = $db->select('quotes', ['id' => $quoteId]);
    if (empty($quotes)) {
        jsonError('Quote not found', 404);
    }

    $quote = $quotes[0];

    // Use FreelancerMatcher to find suitable freelancers
    $matcher = new \Core\FreelancerMatcher($db);
    $matches = $matcher->findMatchingFreelancers($quote['location'], $quote['service']);

    jsonSuccess([
        'quote' => $quote,
        'matches' => $matches,
        'total_matches' => count($matches)
    ]);

} catch (Exception $e) {
    error_log("Freelancer matching error: " . $e->getMessage());
    jsonError('Failed to find matches: ' . $e->getMessage(), 500);
}
