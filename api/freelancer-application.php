<?php
/**
 * Freelancer Application API Endpoint
 * Handles freelancer photographer applications
 */

header('Content-Type: application/json');

// Only allow POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);
    exit;
}

// Get JSON input
$input = file_get_contents('php://input');
$data = json_decode($input, true);

// Validate required fields
$required = ['name', 'email', 'phone', 'city', 'experience', 'specialization'];
foreach ($required as $field) {
    if (empty($data[$field])) {
        http_response_code(400);
        echo json_encode(['error' => "Missing required field: $field"]);
        exit;
    }
}

// Validate email
if (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid email address']);
    exit;
}

// Validate specialization is array
if (!is_array($data['specialization']) || count($data['specialization']) === 0) {
    http_response_code(400);
    echo json_encode(['error' => 'At least one specialization is required']);
    exit;
}

require_once __DIR__ . '/../includes/database.php';
require_once __DIR__ . '/../includes/config.php';

try {
    $db = new DatabaseClient();

    // Prepare data for database
    $applicationData = [
        'name' => trim($data['name']),
        'email' => trim($data['email']),
        'phone' => trim($data['phone']),
        'city' => trim($data['city']),
        'experience' => $data['experience'],
        'specialization' => json_encode($data['specialization']),
        'portfolio_url' => !empty($data['portfolio']) ? trim($data['portfolio']) : null,
        'message' => !empty($data['message']) ? trim($data['message']) : null,
        'status' => 'pending',
        'created_at' => date('Y-m-d H:i:s')
    ];

    // Insert into database
    $result = $db->insert('freelancer_applications', $applicationData);

    // Send email notification
    $to = env('ADMIN_EMAIL', 'info@mekanfotografcisi.tr');
    $subject = 'Yeni Freelancer Başvurusu - ' . $data['name'];

    $emailBody = "Yeni bir freelancer başvurusu alındı:\n\n";
    $emailBody .= "Ad Soyad: " . $data['name'] . "\n";
    $emailBody .= "E-posta: " . $data['email'] . "\n";
    $emailBody .= "Telefon: " . $data['phone'] . "\n";
    $emailBody .= "Şehir: " . $data['city'] . "\n";
    $emailBody .= "Deneyim: " . $data['experience'] . "\n";
    $emailBody .= "Uzmanlık Alanları: " . implode(', ', $data['specialization']) . "\n";

    if (!empty($data['portfolio'])) {
        $emailBody .= "Portfolio: " . $data['portfolio'] . "\n";
    }

    if (!empty($data['message'])) {
        $emailBody .= "\nMesaj:\n" . $data['message'] . "\n";
    }

    $headers = "From: noreply@mekanfotografcisi.tr\r\n";
    $headers .= "Reply-To: " . $data['email'] . "\r\n";
    $headers .= "Content-Type: text/plain; charset=UTF-8\r\n";

    @mail($to, $subject, $emailBody, $headers);

    // Return success
    http_response_code(200);
    echo json_encode([
        'success' => true,
        'message' => 'Application submitted successfully',
        'id' => $result['id'] ?? null
    ]);

} catch (Exception $e) {
    error_log("Freelancer application error: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(['error' => 'Failed to submit application']);
}
