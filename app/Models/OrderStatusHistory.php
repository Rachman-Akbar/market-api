<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

final class OrderStatusHistory extends Model
{
    protected $table = 'order_status_histories';

    protected $fillable = [
        'order_id',
        'from_status',
        'to_status',
        'note',
        'changed_by',
    ];

    protected $casts = [
        'order_id' => 'integer',
        'changed_by' => 'integer',
    ];

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function changedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'changed_by');
    }
}