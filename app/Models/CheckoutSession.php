<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

final class CheckoutSession extends Model
{
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
        'midtrans_payload' => 'array',
        'payment_instructions' => 'array',
        'manual_transfer_payload' => 'array',
        'cart_snapshot' => 'array',
        'shipping_address' => 'array',
        'subtotal' => 'decimal:2',
        'shipping_cost' => 'decimal:2',
        'discount_total' => 'decimal:2',
        'tax_total' => 'decimal:2',
        'grand_total' => 'decimal:2',
        'manual_verified_at' => 'datetime',
        'paid_at' => 'datetime',
        'expires_at' => 'datetime',
    ];

    public function items(): HasMany
    {
        return $this->hasMany(CheckoutSessionItem::class);
    }
}