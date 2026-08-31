<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class Order extends Model
{
    public const STATUS_PENDING = 'pending';

    public const STATUS_AWAITING_PAYMENT = 'awaiting_payment';

    public const STATUS_PAID = 'paid';

    public const STATUS_PROCESSING = 'processing';

    public const STATUS_SHIPPED = 'shipped';

    public const STATUS_DELIVERED = 'delivered';

    public const STATUS_CANCELLED = 'cancelled';

    public const STATUS_REFUNDED = 'refunded';

    /**
     * The normal, linear happy-path a shipment progresses through.
     * Used to render the tracking timeline (cancelled/refunded are shown separately).
     */
    public const TRACKING_STEPS = [
        self::STATUS_PENDING => 'Order Placed',
        self::STATUS_PAID => 'Payment Confirmed',
        self::STATUS_PROCESSING => 'Processing',
        self::STATUS_SHIPPED => 'Shipped',
        self::STATUS_DELIVERED => 'Delivered',
    ];

    protected $fillable = [
        'user_id', 'order_number', 'tracking_token', 'customer_name', 'customer_email',
        'customer_phone', 'status', 'subtotal', 'discount_total', 'shipping_total',
        'tax_total', 'grand_total', 'coupon_id', 'shipping_address_id', 'billing_address_id',
        'shipping_rate_id', 'carrier', 'tracking_number', 'carrier_tracking_url',
        'shipped_at', 'delivered_at', 'estimated_delivery_at', 'notes',
    ];

    protected $casts = [
        'subtotal' => 'decimal:2',
        'discount_total' => 'decimal:2',
        'shipping_total' => 'decimal:2',
        'tax_total' => 'decimal:2',
        'grand_total' => 'decimal:2',
        'shipped_at' => 'datetime',
        'delivered_at' => 'datetime',
        'estimated_delivery_at' => 'date',
    ];

    protected static function booted(): void
    {
        static::creating(function (Order $order) {
            $order->order_number ??= 'ORD-'.strtoupper(Str::random(10));
            $order->tracking_token ??= Str::random(40);
        });
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function coupon(): BelongsTo
    {
        return $this->belongsTo(Coupon::class);
    }

    public function shippingAddress(): BelongsTo
    {
        return $this->belongsTo(Address::class, 'shipping_address_id');
    }

    public function billingAddress(): BelongsTo
    {
        return $this->belongsTo(Address::class, 'billing_address_id');
    }

    public function shippingRate(): BelongsTo
    {
        return $this->belongsTo(ShippingRate::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }

    public function statusHistories(): HasMany
    {
        return $this->hasMany(OrderStatusHistory::class)->orderBy('created_at');
    }

    public function latestPayment(): ?Payment
    {
        return $this->payments()->latest()->first();
    }

    public function isCancelledOrRefunded(): bool
    {
        return in_array($this->status, [self::STATUS_CANCELLED, self::STATUS_REFUNDED], true);
    }

    /**
     * Timeline steps for the order-tracking page: each happy-path status plus
     * whether it has been reached and when.
     */
    public function trackingTimeline(): array
    {
        $reachedStatuses = $this->statusHistories->pluck('created_at', 'status');
        $currentIndex = array_search($this->status, array_keys(self::TRACKING_STEPS), true);

        return collect(self::TRACKING_STEPS)->map(function (string $label, string $status) use ($reachedStatuses, $currentIndex) {
            $stepIndex = array_search($status, array_keys(self::TRACKING_STEPS), true);

            return [
                'status' => $status,
                'label' => $label,
                'completed' => $currentIndex !== false && $stepIndex <= $currentIndex && ! $this->isCancelledOrRefunded(),
                'at' => $reachedStatuses->get($status),
            ];
        })->values()->all();
    }

    public function publicTrackingUrl(): string
    {
        return route('orders.track.show', ['order' => $this->order_number, 'token' => $this->tracking_token]);
    }

    /**
     * Whether the given viewer may see this order's details: either they
     * present the correct (non-empty) tracking token, or they're logged in
     * as the account that actually placed it. Deliberately never true from
     * two null/absent values — a guest order (user_id null) must not be
     * viewable by an anonymous visitor without the token.
     */
    public function isViewableBy(?string $providedToken, ?int $viewerUserId): bool
    {
        if ($providedToken !== null && $providedToken !== '' && hash_equals($this->tracking_token, $providedToken)) {
            return true;
        }

        return $viewerUserId !== null && $this->user_id !== null && $viewerUserId === $this->user_id;
    }
}
