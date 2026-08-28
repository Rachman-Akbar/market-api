<?php

declare(strict_types=1);

namespace App\Domains\PPOB\Infrastructure\Persistence\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class PpoTransactionModel extends Model
{
    use SoftDeletes;

    protected $table = 'ppob_transactions';

    protected $fillable = [
        'reference_id',
        'user_id',
        'operator_id',
        'product_id',
        'provider_product_code',
        'product_name',
        'category',
        'product_type',
        'customer_id',
        'customer_name',
        'bill_amount',
        'provider_price',
        'admin_fee',
        'commission',
        'margin',
        'revenue',
        'net_profit',
        'total_amount',
        'status',
        'provider_status',
        'provider_message',
        'tr_id',
        'sn',
        'pin',
        'provider_raw_response',
        'callback_signature',
        'paid_at',
        'completed_at',
        'expires_at',
        'cancelled_at',
        'metadata',
        'is_active',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'user_id' => 'string',
        'operator_id' => 'integer',
        'product_id' => 'integer',
        'bill_amount' => 'decimal:2',
        'provider_price' => 'decimal:2',
        'admin_fee' => 'decimal:2',
        'commission' => 'decimal:2',
        'margin' => 'decimal:2',
        'revenue' => 'decimal:2',
        'net_profit' => 'decimal:2',
        'total_amount' => 'decimal:2',
        'provider_raw_response' => 'array',
        'metadata' => 'array',
        'is_active' => 'boolean',
        'paid_at' => 'datetime',
        'completed_at' => 'datetime',
        'expires_at' => 'datetime',
        'cancelled_at' => 'datetime',
        'created_by' => 'string',
        'updated_by' => 'string',
    ];

    public function user()
    {
        return $this->belongsTo(\App\Domains\Identity\User\Domain\Entities\User::class, 'user_id');
    }

    public function operator()
    {
        return $this->belongsTo(PpoOperatorModel::class, 'operator_id');
    }

    public function product()
    {
        return $this->belongsTo(PpoProductModel::class, 'product_id');
    }

    public function logs()
    {
        return $this->hasMany(PpoTransactionLogModel::class, 'ppob_transaction_id');
    }

    public function financeEntries()
    {
        return $this->hasMany(PpoFinanceEntryModel::class, 'ppob_transaction_id');
    }
}
