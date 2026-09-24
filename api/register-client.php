<?php
/**
 * Client Registration API
 * Creates a login account (users, role=client) and auto-logs in.
 * Clients don't get a profile row like freelancers do - name/phone/email on
 * the users row is enough; quotes they submit are linked via quotes.user_id.
 */

require_once __DIR__ . '/middleware.php';
require_once __DIR__ . '/../includes/database.php';
require_once __DIR__ . '/../includes/helpers.php';

addCorsHeaders();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    jsonError('Method not allowed', 405);
}

$data = json_decode(file_get_contents('php://input'), true);
if (!is_array($data)) {
    jsonError('Invalid JSON body', 400);
}

$required = ['name', 'email', 'password'];
foreach ($required as $field) {
    if (empty($data[$field])) {
        jsonError("Missing required field: $field", 400);
    }
}

$email = trim($data['email']);
$password = (string) $data['password'];
$name = sanitizeString($data['name']);

if (!isValidEmail($email)) {
    jsonError('Invalid email address', 400);
}

if (strlen($password) < 8) {
    jsonError('Password must be at least 8 characters', 400);
}

$db = new DatabaseClient();

try {
    $existing = $db->select('users', ['email.eq' => $email]);
    if (!empty($existing)) {
        jsonError('An account with this email already exists', 409);
    }

    $user = $db->insert('users', [
        'email' => $email,
        'password_hash' => password_hash($password, PASSWORD_DEFAULT),
        'role' => 'client',
        'name' => $name,
        'phone' => !empty($data['phone']) ? sanitizeString($data['phone']) : null,
    ]);

    $token = mf_generate_jwt($user['id'], $email, $name, 'client');

    jsonSuccess([
        'token' => $token,
        'user' => ['id' => $user['id'], 'email' => $email, 'name' => $name, 'role' => 'client'],
    ]);
} catch (Exception $e) {
    error_log('register-client error: ' . $e->getMessage());
    jsonError('Failed to create account', 500);
}
