<?php
/**
 * Stripe Webhook Endpoint
 * The durable source of truth for payment status - not gated by JWT/session
 * (Stripe can't carry those), authenticated instead via the Stripe-Signature
 * header per Core\PaymentService::constructWebhookEvent(). Idempotent: safe to
 * receive the same event more than once.
 */

require_once __DIR__ . '/../../includes/database.php';
require_once __DIR__ . '/../../includes/Core/PaymentService.php';

$payload = file_get_contents('php://input');
$sigHeader = $_SERVER['HTTP_STRIPE_SIGNATURE'] ?? '';

header('Content-Type: application/json');

try {
    $paymentService = new \Core\PaymentService();
    $event = $paymentService->constructWebhookEvent($payload, $sigHeader);
} catch (Exception $e) {
    error_log('payments/webhook signature verification failed: ' . $e->getMessage());
    http_response_code(400);
    echo json_encode(['error' => 'Invalid signature']);
    exit;
}

$db = new DatabaseClient();

try {
    $type = $event->type;
    $object = $event->data->object;

    if (in_array($type, ['checkout.session.completed', 'checkout.session.async_payment_succeeded'], true)) {
        $payments = $db->select('payments', ['provider_session_id' => $object->id]);
        if (!empty($payments)) {
            $db->update('payments', [
                'status' => 'succeeded',
                'provider_payment_intent_id' => $object->payment_intent ?? null,
                'raw_response' => json_encode($object->toArray()),
                'updated_at' => date('Y-m-d H:i:s'),
            ], ['id' => $payments[0]['id']]);
        }
    } elseif (in_array($type, ['checkout.session.expired', 'checkout.session.async_payment_failed'], true)) {
        $payments = $db->select('payments', ['provider_session_id' => $object->id]);
        if (!empty($payments)) {
            $db->update('payments', [
                'status' => 'failed',
                'failure_reason' => $type,
                'raw_response' => json_encode($object->toArray()),
                'updated_at' => date('Y-m-d H:i:s'),
            ], ['id' => $payments[0]['id']]);
        }
    }

    http_response_code(200);
    echo json_encode(['received' => true]);
} catch (Exception $e) {
    error_log('payments/webhook processing error: ' . $e->getMessage());
    http_response_code(500);
    echo json_encode(['error' => 'Processing failed']);
}
