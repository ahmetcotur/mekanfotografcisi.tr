<?php
/**
 * Payment Service (Stripe)
 * Thin wrapper around the Stripe SDK, matching the MailService/FreelancerMatcher
 * convention of one small service class per concern. Scope is deliberately
 * minimal: a single Checkout Session per payment, no subscriptions/escrow.
 */

namespace Core;

use Stripe\StripeClient;
use Stripe\Webhook;

class PaymentService
{
    private $client;

    public function __construct()
    {
        $secretKey = \env('STRIPE_SECRET_KEY');
        if (empty($secretKey)) {
            throw new \Exception('STRIPE_SECRET_KEY is not configured');
        }
        $this->client = new StripeClient($secretKey);
    }

    /**
     * Create a Checkout Session for a single payment.
     *
     * @param float $amount In the currency's major unit (e.g. TRY, not kuruş)
     * @param string $currency ISO currency code, lowercase for Stripe (e.g. 'try')
     * @param string $description Shown on the Stripe-hosted checkout page
     * @param string $successUrl
     * @param string $cancelUrl
     * @param array $metadata Arbitrary key/value pairs echoed back on the session/webhook
     * @return \Stripe\Checkout\Session
     */
    public function createCheckoutSession($amount, $currency, $description, $successUrl, $cancelUrl, array $metadata = [])
    {
        return $this->client->checkout->sessions->create([
            'mode' => 'payment',
            'payment_method_types' => ['card'],
            'line_items' => [[
                'price_data' => [
                    'currency' => strtolower($currency),
                    'product_data' => ['name' => $description],
                    'unit_amount' => (int) round($amount * 100),
                ],
                'quantity' => 1,
            ]],
            'success_url' => $successUrl,
            'cancel_url' => $cancelUrl,
            'metadata' => $metadata,
        ]);
    }

    /**
     * Re-fetch a session from Stripe (never trust the browser redirect alone).
     */
    public function retrieveCheckoutSession($sessionId)
    {
        return $this->client->checkout->sessions->retrieve($sessionId);
    }

    /**
     * Verify and construct a webhook event from the raw request body + signature
     * header. Throws on an invalid signature.
     */
    public function constructWebhookEvent($payload, $signatureHeader)
    {
        $webhookSecret = \env('STRIPE_WEBHOOK_SECRET');
        if (empty($webhookSecret)) {
            throw new \Exception('STRIPE_WEBHOOK_SECRET is not configured');
        }
        return Webhook::constructEvent($payload, $signatureHeader, $webhookSecret);
    }
}
