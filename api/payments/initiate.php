<?php
/**
 * Payments Initiate API
 * Starts a Stripe Checkout Session for a booking deposit/commission tied to a
 * quote_assignment. The caller must be the client who owns the underlying quote,
 * or an admin. Amount is supplied by the caller (agreed out-of-band with the
 * photographer) - there is no pricing engine in this minimal scope.
 */

require_once __DIR__ . '/../middleware.php';
require_once __DIR__ . '/../../includes/database.php';
require_once __DIR__ . '/../../includes/Core/PaymentService.php';

addCorsHeaders();
$authUser = requireAuth();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    jsonError('Method not allowed', 405);
}

$data = json_decode(file_get_contents('php://input'), true);
$assignmentId = $data['quote_assignment_id'] ?? null;
$amount = $data['amount'] ?? null;
$type = $data['type'] ?? 'booking_deposit';

if (empty($assignmentId) || empty($amount) || !is_numeric($amount) || $amount <= 0) {
    jsonError('quote_assignment_id and a positive numeric amount are required', 400);
}
if (!in_array($type, ['booking_deposit', 'commission', 'full_payment'], true)) {
    jsonError('Invalid payment type', 400);
}

$db = new DatabaseClient();

try {
    $assignments = $db->select('quote_assignments', ['id' => $assignmentId]);
    if (empty($assignments)) {
        jsonError('Assignment not found', 404);
    }
    $assignment = $assignments[0];

    $quotes = $db->select('quotes', ['id' => $assignment['quote_id']]);
    $quote = $quotes[0] ?? null;

    $isAdmin = ($authUser['role'] ?? 'admin') === 'admin';
    $isOwningClient = $quote && !empty($quote['user_id']) && $quote['user_id'] === ($authUser['user_id'] ?? null);
    if (!$isAdmin && !$isOwningClient) {
        jsonError('Forbidden', 403);
    }

    $siteUrl = rtrim(env('SITE_URL', 'https://mekanfotografcisi.tr'), '/');

    $payment = $db->insert('payments', [
        'quote_assignment_id' => $assignmentId,
        'quote_id' => $assignment['quote_id'],
        'freelancer_id' => $assignment['freelancer_id'],
        'payer_user_id' => $authUser['user_id'] ?? null,
        'type' => $type,
        'amount' => $amount,
        'currency' => 'TRY',
        'status' => 'pending',
    ]);

    try {
        $paymentService = new \Core\PaymentService();
        $session = $paymentService->createCheckoutSession(
            (float) $amount,
            'try',
            'Çekim Talebi #' . $assignment['quote_id'] . ' - ' . ucfirst(str_replace('_', ' ', $type)),
            $siteUrl . '/api/payments/callback.php?payment_id=' . $payment['id'] . '&session_id={CHECKOUT_SESSION_ID}',
            $siteUrl . ($isAdmin ? '/admin/' : '/musteri'),
            ['payment_id' => $payment['id'], 'quote_assignment_id' => (string) $assignmentId]
        );
    } catch (Exception $e) {
        // Don't leave an orphaned pending row with no Stripe session behind it.
        $db->delete('payments', ['id' => $payment['id']]);
        throw $e;
    }

    $db->update('payments', ['provider_session_id' => $session->id], ['id' => $payment['id']]);

    jsonSuccess(['payment_id' => $payment['id'], 'checkout_url' => $session->url]);
} catch (Exception $e) {
    error_log('payments/initiate error: ' . $e->getMessage());
    jsonError('Server error: ' . $e->getMessage(), 500);
}
