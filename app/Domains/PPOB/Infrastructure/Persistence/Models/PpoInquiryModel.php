<?php

declare(strict_types=1);

namespace App\Domains\PPOB\Infrastructure\Persistence\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class PpoInquiryModel extends Model
{
    use SoftDeletes;

    protected $table = 'ppob_inquiries';

    protected $fillable = [
        'reference_id',
        'user_id',
        'operator_id',
        'product_code',
        'category',
        'customer_id',
        'tr_id',
        'customer_name',
        'customer_no',
        'bill_amount',
        'admin_charge',
        'admin_charge_message',
        'detail',
        'status',
        'expires_at',
    ];

    protected $casts = [
        'user_id' => 'string',
        'operator_id' => 'integer',
        'bill_amount' => 'decimal:2',
        'admin_charge' => 'decimal:2',
        'detail' => 'array',
        'expires_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(\App\Domains\Identity\User\Domain\Entities\User::class, 'user_id');
    }

    public function operator()
    {
        return $this->belongsTo(PpoOperatorModel::class, 'operator_id');
    }
}
