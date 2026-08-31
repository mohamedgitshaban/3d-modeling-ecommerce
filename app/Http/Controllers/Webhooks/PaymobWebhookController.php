<?php

namespace App\Http\Controllers\Webhooks;

use App\Events\OrderPaid;
use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Payment;
use App\Services\Payments\PaymentGatewayContract;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class PaymobWebhookController extends Controller
{
    public function __construct(protected PaymentGatewayContract $gateway) {}

    public function handleTransaction(Request $request): JsonResponse
    {
        $payload = $request->input('obj', []);
        $providedHmac = (string) $request->query('hmac');

        if (! $this->gateway->verifyWebhookSignature($payload, $providedHmac)) {
            Log::warning('Paymob webhook rejected: invalid HMAC.', ['payload' => $payload]);

            return response()->json(['message' => 'Invalid signature'], 401);
        }

        $merchantOrderId = data_get($payload, 'order.merchant_order_id');
        $order = Order::where('order_number', $merchantOrderId)->first();

        if (! $order) {
            Log::warning('Paymob webhook: unknown order.', ['merchant_order_id' => $merchantOrderId]);

            return response()->json(['message' => 'Order not found'], 404);
        }

        $success = (bool) data_get($payload, 'success');
        $payment = $order->payments()->latest()->first() ?? Payment::create([
            'order_id' => $order->id,
            'gateway' => 'paymob',
            'amount' => $order->grand_total,
        ]);

        $payment->update([
            'transaction_id' => data_get($payload, 'id'),
            'paymob_order_id' => data_get($payload, 'order.id'),
            'status' => $success ? 'success' : 'failed',
            'raw_response' => $payload,
        ]);

        if ($success && $order->status !== Order::STATUS_PAID) {
            OrderPaid::dispatch($order);
        } elseif (! $success) {
            $order->update(['status' => Order::STATUS_PENDING]);
        }

        return response()->json(['message' => 'ok']);
    }
}
