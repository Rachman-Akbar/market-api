<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

final class PaymentAttempt extends Model
{
    protected $table = 'payment_attempts';

    protected $fillable = [
        'order_id',
        'gateway',
        'gateway_order_id',
        'gateway_transaction_id',
        'snap_token',
        'redirect_url',
        'status',
        'payment_type',
        'transaction_status',
        'fraud_status',
        'currency',
        'gross_amount',
        'request_payload',
        'response_payload',
        'latest_notification_payload',
        'payment_instructions',
        'paid_at',
        'expires_at',
    ];

    protected $casts = [
        'order_id' => 'integer',
        'gross_amount' => 'integer',
        'request_payload' => 'array',
        'response_payload' => 'array',
        'latest_notification_payload' => 'array',
        'payment_instructions' => 'array',
        'paid_at' => 'datetime',
        'expires_at' => 'datetime',
    ];

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function isPending(): bool
    {
        return $this->status === 'pending';
    }

    public function isPaid(): bool
    {
        return $this->status === 'paid';
    }

    public function isExpired(): bool
    {
        return $this->status === 'expired';
    }

    public function isFailed(): bool
    {
        return in_array($this->status, ['failed', 'cancelled', 'expired'], true);
    }

    public function markAsPending(array $payload = []): void
    {
        $this->forceFill([
            'status' => 'pending',
            'latest_notification_payload' => $payload ?: $this->latest_notification_payload,
        ])->save();
    }

    public function markAsPaid(array $payload = []): void
    {
        $this->forceFill([
            'status' => 'paid',
            'latest_notification_payload' => $payload ?: $this->latest_notification_payload,
            'paid_at' => $this->paid_at ?: now(),
        ])->save();
    }

    public function markAsFailed(string $status = 'failed', array $payload = []): void
    {
        $allowed = ['failed', 'cancelled', 'expired'];

        $this->forceFill([
            'status' => in_array($status, $allowed, true) ? $status : 'failed',
            'latest_notification_payload' => $payload ?: $this->latest_notification_payload,
        ])->save();
    }
}