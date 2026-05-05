<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

final class MidtransNotification extends Model
{
    protected $table = 'midtrans_notifications';

    public $timestamps = false;

    protected $fillable = [
        'payment_attempt_id',
        'order_id',
        'gateway_order_id',
        'gateway_transaction_id',
        'transaction_status',
        'signature_key',
        'payload_hash',
        'payload',
        'received_at',
    ];

    protected $casts = [
        'payment_attempt_id' => 'integer',
        'order_id' => 'integer',
        'payload' => 'array',
        'received_at' => 'datetime',
    ];

    public function paymentAttempt(): BelongsTo
    {
        return $this->belongsTo(PaymentAttempt::class);
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }
}