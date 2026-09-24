<?php
/**
 * Freelancer Registration API
 * Creates a login account (users, role=freelancer) plus the linked
 * freelancer_applications profile row in one step, then auto-logs in.
 *
 * This is the self-service counterpart to the older api/freelancer-application.php
 * (kept as-is for the simple homepage-modal path). Anyone registering here gets a
 * dashboard immediately, showing their application as 'pending' until an admin
 * approves it in admin-spa.
 */

require_once __DIR__ . '/middleware.php';
require_once __DIR__ . '/../includes/database.php';
require_once __DIR__ . '/../includes/helpers.php';
require_once __DIR__ . '/../includes/Core/MailService.php';

addCorsHeaders();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    jsonError('Method not allowed', 405);
}

$data = json_decode(file_get_contents('php://input'), true);
if (!is_array($data)) {
    jsonError('Invalid JSON body', 400);
}

$required = ['name', 'email', 'password', 'phone', 'city', 'experience', 'specialization'];
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

if (!is_array($data['specialization']) || count($data['specialization']) === 0) {
    jsonError('At least one specialization is required', 400);
}

$db = new DatabaseClient();

try {
    $existing = $db->select('users', ['email.eq' => $email]);
    if (!empty($existing)) {
        jsonError('An account with this email already exists', 409);
    }

    // Build a unique slug for the public profile URL (/fotografcilar/{slug}).
    $baseSlug = to_permalink($name);
    if ($baseSlug === '') {
        $baseSlug = 'fotografci';
    }
    $slug = $baseSlug;
    $suffix = 1;
    while (!empty($db->select('freelancer_applications', ['slug' => $slug]))) {
        $suffix++;
        $slug = $baseSlug . '-' . $suffix;
    }

    $connection = $db->getConnection();
    $connection->beginTransaction();

    try {
        $user = $db->insert('users', [
            'email' => $email,
            'password_hash' => password_hash($password, PASSWORD_DEFAULT),
            'role' => 'freelancer',
            'name' => $name,
            'phone' => sanitizeString($data['phone']),
        ]);

        $profile = $db->insert('freelancer_applications', [
            'user_id' => $user['id'],
            'name' => $name,
            'email' => $email,
            'phone' => sanitizeString($data['phone']),
            'city' => sanitizeString($data['city']),
            'experience' => $data['experience'],
            'specialization' => json_encode($data['specialization']),
            'portfolio_url' => !empty($data['portfolio_url']) ? trim($data['portfolio_url']) : null,
            'message' => !empty($data['message']) ? sanitizeString($data['message']) : null,
            'slug' => $slug,
            'status' => 'pending',
        ]);

        $connection->commit();
    } catch (Exception $e) {
        $connection->rollBack();
        throw $e;
    }

    // Best-effort notifications, matching api/freelancer-application.php's existing
    // pattern; failures here must not block account creation.
    try {
        $mailer = new \Core\MailService();
        $adminEmail = env('ADMIN_EMAIL', 'info@mekanfotografcisi.tr');
        $body = "Yeni bir freelancer hesabı oluşturuldu:\n\n";
        $body .= "Ad Soyad: {$name}\nE-posta: {$email}\nTelefon: {$data['phone']}\nŞehir: {$data['city']}\n";
        $body .= "Uzmanlık: " . implode(', ', $data['specialization']) . "\n";
        $mailer->send($adminEmail, 'Yeni Freelancer Kaydı - ' . $name, $body, $email);
    } catch (Exception $e) {
        error_log('register-freelancer notification error: ' . $e->getMessage());
    }

    $token = mf_generate_jwt($user['id'], $email, $name, 'freelancer');

    jsonSuccess([
        'token' => $token,
        'user' => ['id' => $user['id'], 'email' => $email, 'name' => $name, 'role' => 'freelancer'],
        'profile' => ['id' => $profile['id'], 'slug' => $slug, 'status' => 'pending'],
    ]);
} catch (Exception $e) {
    error_log('register-freelancer error: ' . $e->getMessage());
    jsonError('Failed to create account', 500);
}
