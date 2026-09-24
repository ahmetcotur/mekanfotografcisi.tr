<?php
/**
 * Payments Callback (Stripe success_url)
 * Browser redirect target after Stripe Checkout. Never trusts the redirect
 * alone - re-fetches the session from Stripe to confirm payment_status before
 * updating our own payments row. The webhook (webhook.php) is the durable
 * source of truth for async/delayed confirmations; this just gives the
 * browser a fast redirect with an accurate status.
 */

require_once __DIR__ . '/../../includes/database.php';
require_once __DIR__ . '/../../includes/Core/PaymentService.php';

$paymentId = $_GET['payment_id'] ?? null;
$sessionId = $_GET['session_id'] ?? null;

$db = new DatabaseClient();
$redirectBase = rtrim(env('SITE_URL', ''), '/');

if (empty($paymentId) || empty($sessionId)) {
    header('Location: ' . $redirectBase . '/musteri?payment=error');
    exit;
}

try {
    $payments = $db->select('payments', ['id' => $paymentId]);
    if (empty($payments)) {
        header('Location: ' . $redirectBase . '/musteri?payment=error');
        exit;
    }
    $payment = $payments[0];

    $paymentService = new \Core\PaymentService();
    $session = $paymentService->retrieveCheckoutSession($sessionId);

    if ($session->id !== $payment['provider_session_id']) {
        // Session id doesn't match what we stored for this payment - refuse to update.
        header('Location: ' . $redirectBase . '/musteri?payment=error');
        exit;
    }

    $newStatus = $session->payment_status === 'paid' ? 'succeeded' : $payment['status'];

    $db->update('payments', [
        'status' => $newStatus,
        'provider_payment_intent_id' => $session->payment_intent,
        'raw_response' => json_encode($session->toArray()),
        'updated_at' => date('Y-m-d H:i:s'),
    ], ['id' => $paymentId]);

    header('Location: ' . $redirectBase . '/musteri?payment=' . ($newStatus === 'succeeded' ? 'success' : 'pending'));
    exit;
} catch (Exception $e) {
    error_log('payments/callback error: ' . $e->getMessage());
    header('Location: ' . $redirectBase . '/musteri?payment=error');
    exit;
}
