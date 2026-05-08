<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

final class CheckoutSession extends Model
{
    protected $table = 'checkout_sessions';

    protected $fillable = [
        'session_number',
        'user_id',
        'status',
        'payment_gateway',
        'payment_method',

        'midtrans_order_id',
        'midtrans_transaction_id',
        'midtrans_snap_token',
        'midtrans_redirect_url',
        'midtrans_payment_type',
        'midtrans_transaction_status',
        'midtrans_fraud_status',
        'midtrans_payload',
        'payment_instructions',

        'manual_transfer_payload',
        'manual_transfer_proof_path',
        'manual_verified_by',
        'manual_verified_at',

        'currency',
        'subtotal',
        'shipping_cost',
        'discount_total',
        'tax_total',
        'grand_total',

        'cart_snapshot',
        'shipping_address',
        'notes',

        'created_order_id',
        'paid_at',
        'expires_at',
    ];

    protected $casts = [
        'cart_snapshot' => 'array',
        'shipping_address' => 'array',
        'midtrans_payload' => 'array',
        'payment_instructions' => 'array',
        'manual_transfer_payload' => 'array',

        'subtotal' => 'decimal:2',
        'shipping_cost' => 'decimal:2',
        'discount_total' => 'decimal:2',
        'tax_total' => 'decimal:2',
        'grand_total' => 'decimal:2',

        'paid_at' => 'datetime',
        'expires_at' => 'datetime',
        'manual_verified_at' => 'datetime',
    ];

    public function items(): HasMany
    {
        return $this->hasMany(CheckoutSessionItem::class);
    }

    public function paymentAttempts(): HasMany
    {
        return $this->hasMany(PaymentAttempt::class);
    }

    public function latestPaymentAttempt(): HasOne
    {
        return $this->hasOne(PaymentAttempt::class)->latestOfMany();
    }

    public function isFinalized(): bool
    {
        return $this->created_order_id !== null;
    }

    public function isPayable(): bool
    {
        return in_array($this->status, ['draft', 'pending_payment'], true)
            && $this->created_order_id === null;
    }
}
