<?php

namespace App\Services\Payments;

class PaymentInitiationResult
{
    public function __construct(
        public string $redirectUrl,
        public ?string $gatewayOrderId = null,
        public array $raw = [],
    ) {}
}
