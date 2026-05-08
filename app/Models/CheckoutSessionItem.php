<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

final class CheckoutSessionItem extends Model
{
    protected $table = 'checkout_session_items';

    protected $fillable = [
        'checkout_session_id',
        'product_id',
        'product_name',
        'sku',
        'quantity',
        'currency',
        'unit_price',
        'subtotal',
    ];

    protected $casts = [
        'checkout_session_id' => 'integer',
        'product_id' => 'integer',
        'quantity' => 'integer',
        'unit_price' => 'decimal:2',
        'subtotal' => 'decimal:2',
    ];

    public function checkoutSession(): BelongsTo
    {
        return $this->belongsTo(CheckoutSession::class);
    }
}
