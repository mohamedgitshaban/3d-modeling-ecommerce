<?php

namespace App\Services\Payments;

use App\Models\Order;

interface PaymentGatewayContract
{
    /**
     * Start a payment for the given order and return a URL/token the
     * customer should be redirected to (or shown, e.g. in an iframe).
     */
    public function initiate(Order $order, string $method = 'card'): PaymentInitiationResult;

    /**
     * Verify an inbound webhook payload's signature is genuinely from the gateway.
     */
    public function verifyWebhookSignature(array $payload, string $providedHmac): bool;
}
