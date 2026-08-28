<?php

declare(strict_types=1);

namespace App\Domains\PPOB\Infrastructure\Persistence\Models;

use Illuminate\Database\Eloquent\Model;

class PpoTransactionLogModel extends Model
{
    public $timestamps = true;

    protected $table = 'ppob_transaction_logs';

    protected $fillable = [
        'ppob_transaction_id',
        'reference_id',
        'action',
        'direction',
        'request_payload',
        'response_payload',
        'http_status',
        'provider_status',
        'provider_message',
        'ip_address',
    ];

    protected $casts = [
        'ppob_transaction_id' => 'integer',
        'request_payload' => 'array',
        'response_payload' => 'array',
        'http_status' => 'integer',
    ];

    public function transaction()
    {
        return $this->belongsTo(PpoTransactionModel::class, 'ppob_transaction_id');
    }
}
