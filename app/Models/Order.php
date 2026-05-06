<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

final class Order extends Model
{
    protected $table = 'orders';

    protected $fillable = [
        'order_number',
        'midtrans_order_id',
        'user_id',
        'status',
        'payment_status',
        'currency',
        'subtotal',
        'shipping_cost',
        'discount_total',
        'tax_total',
        'grand_total',
        'shipping_address',
        'payment_method',
        'payment_gateway',
        'midtrans_transaction_id',
        'midtrans_snap_token',
        'midtrans_redirect_url',
        'midtrans_payment_type',
        'midtrans_transaction_status',
        'midtrans_fraud_status',
        'midtrans_payload',
        'payment_instructions',
        'paid_at',
        'payment_expires_at',
        'notes',
    ];

    protected $casts = [
    'shipping_address' => 'array',
    'source_cart_item_ids' => 'array',
    'midtrans_payload' => 'array',
    'payment_instructions' => 'array',
    'paid_at' => 'datetime',
    'payment_expires_at' => 'datetime',
];

    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    public function statusHistories(): HasMany
    {
        return $this->hasMany(OrderStatusHistory::class);
    }

    public function paymentAttempts(): HasMany
    {
        return $this->hasMany(PaymentAttempt::class);
    }

    public function latestPaymentAttempt(): HasOne
    {
        return $this->hasOne(PaymentAttempt::class)->latestOfMany();
    }

    public function midtransNotifications(): HasMany
    {
        return $this->hasMany(MidtransNotification::class);
    }

    public function isPaid(): bool
    {
        return $this->payment_status === 'paid';
    }

    public function isPayable(): bool
    {
        return ! in_array($this->payment_status, ['paid', 'refunded', 'cancelled'], true)
            && ! in_array($this->status, ['cancelled', 'delivered'], true);
    }

    public function markPaymentPending(array $payload = []): void
    {
        $this->forceFill([
            'payment_gateway' => 'midtrans',
            'payment_status' => 'pending',
            'midtrans_payload' => $payload ?: $this->midtrans_payload,
        ])->save();
    }

    public function markAsPaid(array $payload = []): void
    {
        $this->forceFill([
            'payment_status' => 'paid',
            'status' => 'confirmed',
            'midtrans_payload' => $payload ?: $this->midtrans_payload,
            'paid_at' => $this->paid_at ?: now(),
        ])->save();
    }

    public function markPaymentFailed(string $paymentStatus = 'failed', array $payload = []): void
    {
        $allowed = ['failed', 'cancelled'];

        $this->forceFill([
            'payment_status' => in_array($paymentStatus, $allowed, true) ? $paymentStatus : 'failed',
            'status' => 'cancelled',
            'midtrans_payload' => $payload ?: $this->midtrans_payload,
        ])->save();
    }
}
