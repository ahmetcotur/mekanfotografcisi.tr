<?php
/**
 * Client Contact-Freelancer API
 * Public endpoint (works anonymously or logged-in) used from a photographer's
 * public profile page. Creates a direct quote (visibility='direct') plus an
 * immediate quote_assignments row (source='direct_request'), so the request
 * shows up in the target freelancer's dashboard without admin involvement.
 */

require_once __DIR__ . '/../middleware.php';
require_once __DIR__ . '/../../includes/database.php';
require_once __DIR__ . '/../../includes/helpers.php';

addCorsHeaders();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    jsonError('Method not allowed', 405);
}

$data = json_decode(file_get_contents('php://input'), true);
if (!is_array($data)) {
    jsonError('Invalid JSON body', 400);
}

$required = ['freelancer_id', 'name', 'email', 'phone', 'message'];
foreach ($required as $field) {
    if (empty($data[$field])) {
        jsonError("Missing required field: $field", 400);
    }
}

if (!isValidEmail($data['email'])) {
    jsonError('Invalid email address', 400);
}

$db = new DatabaseClient();

try {
    $freelancers = $db->select('freelancer_applications', [
        'id' => $data['freelancer_id'],
        'status' => 'approved',
        'is_public' => true,
    ]);
    if (empty($freelancers)) {
        jsonError('Photographer not found', 404);
    }
    $freelancer = $freelancers[0];

    // Optional: attach the logged-in client's user_id, same convention as save-form.php.
    $userId = null;
    $authHeader = $_SERVER['HTTP_AUTHORIZATION'] ?? $_SERVER['REDIRECT_HTTP_AUTHORIZATION'] ?? '';
    if (!empty($authHeader)) {
        $payload = validateAuthToken();
        if ($payload && ($payload['role'] ?? '') === 'client') {
            $userId = $payload['user_id'];
        }
    }

    $quote = $db->insert('quotes', [
        'name' => sanitizeString($data['name']),
        'email' => trim($data['email']),
        'phone' => sanitizeString($data['phone']),
        'location' => !empty($data['location']) ? sanitizeString($data['location']) : $freelancer['city'],
        'service' => !empty($data['service']) ? sanitizeString($data['service']) : null,
        'message' => sanitizeString($data['message']),
        'status' => 'beklemede',
        'visibility' => 'direct',
        'preferred_freelancer_id' => $freelancer['id'],
        'user_id' => $userId,
        'ip_address' => $_SERVER['REMOTE_ADDR'] ?? null,
    ]);

    $assignment = $db->insert('quote_assignments', [
        'quote_id' => $quote['id'],
        'freelancer_id' => $freelancer['id'],
        'status' => 'pending',
        'source' => 'direct_request',
    ]);

    jsonSuccess(['quote_id' => $quote['id'], 'assignment_id' => $assignment['id']]);
} catch (Exception $e) {
    error_log('client/contact-freelancer error: ' . $e->getMessage());
    jsonError('Server error', 500);
}
