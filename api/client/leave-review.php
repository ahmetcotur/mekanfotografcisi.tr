<?php
/**
 * Client Leave-Review API
 * A client rates the freelancer on a completed assignment they own. One review
 * per assignment (reviews.quote_assignment_id is UNIQUE).
 */

require_once __DIR__ . '/../middleware.php';
require_once __DIR__ . '/../../includes/database.php';
require_once __DIR__ . '/../../includes/helpers.php';
require_once __DIR__ . '/../../includes/Core/ReviewService.php';

addCorsHeaders();
$authUser = requireRole(['client']);

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    jsonError('Method not allowed', 405);
}

$data = json_decode(file_get_contents('php://input'), true);
$assignmentId = $data['quote_assignment_id'] ?? null;
$rating = $data['rating'] ?? null;

if (empty($assignmentId) || !is_numeric($rating) || $rating < 1 || $rating > 5) {
    jsonError('quote_assignment_id and a rating between 1 and 5 are required', 400);
}

$db = new DatabaseClient();

try {
    $assignments = $db->select('quote_assignments', ['id' => $assignmentId]);
    if (empty($assignments)) {
        jsonError('Assignment not found', 404);
    }
    $assignment = $assignments[0];

    if ($assignment['status'] !== 'completed') {
        jsonError('Only completed assignments can be reviewed', 403);
    }

    $quotes = $db->select('quotes', ['id' => $assignment['quote_id']]);
    $quote = $quotes[0] ?? null;
    if (!$quote || $quote['user_id'] !== $authUser['user_id']) {
        jsonError('Forbidden', 403);
    }

    $existing = $db->select('reviews', ['quote_assignment_id' => $assignmentId]);
    if (!empty($existing)) {
        jsonError('This assignment has already been reviewed', 409);
    }

    $reviewService = new \Core\ReviewService($db);
    $review = $reviewService->addReview(
        $assignment['freelancer_id'],
        $assignmentId,
        $authUser['user_id'],
        (int) $rating,
        !empty($data['comment']) ? sanitizeString($data['comment']) : null
    );

    jsonSuccess(['review' => $review]);
} catch (Exception $e) {
    error_log('client/leave-review error: ' . $e->getMessage());
    jsonError('Server error', 500);
}
