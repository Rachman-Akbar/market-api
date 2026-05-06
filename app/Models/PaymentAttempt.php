<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

final class PaymentAttempt extends Model
{
    protected $fillable = [
        'order_id',
        'attempt_no',
        'gateway',
        'gateway_order_id',
        'gateway_transaction_id',
        'snap_token',
        'redirect_url',
        'status',
        'payment_type',
        'transaction_status',
        'fraud_status',
        'failure_reason',
        'provider_response_code',
        'provider_response_message',
        'currency',
        'gross_amount',
        'request_payload',
        'response_payload',
        'latest_notification_payload',
        'payment_instructions',
        'paid_at',
        'expired_at',
        'expires_at',
    ];

    protected $casts = [
        'request_payload' => 'array',
        'response_payload' => 'array',
        'latest_notification_payload' => 'array',
        'payment_instructions' => 'array',
        'paid_at' => 'datetime',
        'expired_at' => 'datetime',
        'expires_at' => 'datetime',
    ];

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }
}
