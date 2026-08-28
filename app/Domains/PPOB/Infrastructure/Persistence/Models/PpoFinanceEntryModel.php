<?php

declare(strict_types=1);

namespace App\Domains\PPOB\Infrastructure\Persistence\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class PpoFinanceEntryModel extends Model
{
    use SoftDeletes;

    protected $table = 'ppob_finance_entries';

    protected $fillable = [
        'source_type',
        'source_id',
        'ppob_transaction_id',
        'reference_id',
        'transaction_type',
        'title',
        'description',
        'amount',
        'status',
        'occurred_at',
        'metadata',
        'is_active',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'source_type' => 'string',
        'source_id' => 'string',
        'ppob_transaction_id' => 'integer',
        'amount' => 'decimal:2',
        'occurred_at' => 'datetime',
        'metadata' => 'array',
        'is_active' => 'boolean',
        'created_by' => 'string',
        'updated_by' => 'string',
    ];

    public function transaction()
    {
        return $this->belongsTo(PpoTransactionModel::class, 'ppob_transaction_id');
    }
}
