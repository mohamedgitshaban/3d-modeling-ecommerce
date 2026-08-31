<?php

namespace App\Services\Payments;

use App\Models\Order;
use Illuminate\Support\Facades\Http;
use RuntimeException;

/**
 * Paymob Accept integration: auth token -> register order -> payment key -> hosted iframe.
 * See https://docs.paymob.com/docs/accept-standard-redirect
 */
class PaymobGateway implements PaymentGatewayContract
{
    /**
     * The exact field order Paymob's HMAC covers for a `transaction` webhook,
     * per their documentation — order and casing matter.
     */
    protected const HMAC_FIELDS = [
        'amount_cents', 'created_at', 'currency', 'error_occured', 'has_parent_transaction',
        'id', 'integration_id', 'is_3d_secure', 'is_auth', 'is_capture', 'is_refunded',
        'is_standalone_payment', 'is_voided', 'order.id', 'owner', 'pending',
        'source_data.pan', 'source_data.sub_type', 'source_data.type', 'success',
    ];

    protected string $baseUrl;

    public function __construct()
    {
        $this->baseUrl = rtrim(config('paymob.base_url'), '/');
    }

    public function initiate(Order $order, string $method = 'card'): PaymentInitiationResult
    {
        $authToken = $this->authenticate();
        $paymobOrderId = $this->registerOrder($authToken, $order);
        $integrationId = config("paymob.integrations.{$method}");

        if (! $integrationId) {
            throw new RuntimeException("No Paymob integration configured for payment method [{$method}].");
        }

        $paymentToken = $this->requestPaymentKey($authToken, $order, $paymobOrderId, (int) $integrationId);

        $iframeId = config('paymob.iframe_id');
        $redirectUrl = "https://accept.paymob.com/api/acceptance/iframes/{$iframeId}?payment_token={$paymentToken}";

        return new PaymentInitiationResult(
            redirectUrl: $redirectUrl,
            gatewayOrderId: (string) $paymobOrderId,
            raw: ['payment_token' => $paymentToken],
        );
    }

    protected function authenticate(): string
    {
        $response = Http::post("{$this->baseUrl}/auth/tokens", [
            'api_key' => config('paymob.api_key'),
        ])->throw();

        return $response->json('token');
    }

    protected function registerOrder(string $authToken, Order $order): int
    {
        $response = Http::post("{$this->baseUrl}/ecommerce/orders", [
            'auth_token' => $authToken,
            'delivery_needed' => false,
            'amount_cents' => (int) round($order->grand_total * 100),
            'currency' => config('paymob.currency'),
            'merchant_order_id' => $order->order_number,
            'items' => $order->items->map(fn ($item) => [
                'name' => $item->name,
                'amount_cents' => (int) round($item->unit_price * 100),
                'quantity' => $item->quantity,
            ])->all(),
        ])->throw();

        return $response->json('id');
    }

    protected function requestPaymentKey(string $authToken, Order $order, int $paymobOrderId, int $integrationId): string
    {
        $billing = [
            'first_name' => $order->customer_name,
            'last_name' => 'N/A',
            'email' => $order->customer_email,
            'phone_number' => $order->customer_phone ?: 'NA',
            'apartment' => 'NA', 'floor' => 'NA', 'street' => $order->shippingAddress?->address_line_1 ?? 'NA',
            'building' => 'NA', 'shipping_method' => 'NA',
            'postal_code' => $order->shippingAddress?->postal_code ?? 'NA',
            'city' => $order->shippingAddress?->city ?? 'NA',
            'country' => $order->shippingAddress?->country ?? 'NA',
            'state' => $order->shippingAddress?->state ?? 'NA',
        ];

        $response = Http::post("{$this->baseUrl}/acceptance/payment_keys", [
            'auth_token' => $authToken,
            'amount_cents' => (int) round($order->grand_total * 100),
            'expiration' => 3600,
            'order_id' => $paymobOrderId,
            'billing_data' => $billing,
            'currency' => config('paymob.currency'),
            'integration_id' => $integrationId,
        ])->throw();

        return $response->json('token');
    }

    public function verifyWebhookSignature(array $payload, string $providedHmac): bool
    {
        $concatenated = collect(self::HMAC_FIELDS)
            ->map(fn ($field) => $this->hmacFieldValue($payload, $field))
            ->implode('');

        $expected = hash_hmac('sha512', $concatenated, (string) config('paymob.hmac_secret'));

        return hash_equals($expected, $providedHmac);
    }

    protected function hmacFieldValue(array $payload, string $dottedField): string
    {
        $value = data_get($payload, $dottedField);

        if (is_bool($value)) {
            return $value ? 'true' : 'false';
        }

        return (string) ($value ?? '');
    }
}
