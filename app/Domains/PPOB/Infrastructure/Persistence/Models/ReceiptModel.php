<?php

declare(strict_types=1);

namespace App\Domains\PPOB\Infrastructure\Persistence\Models;

use App\Domains\Identity\User\Domain\Entities\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * A receipt (bukti pembayaran) for a completed transaction. Unlike an invoice
 * (which is issued to request payment), a receipt proves that payment already
 * happened. It backs both marketplace orders and digital (PPOB) transactions.
 */
class ReceiptModel extends Model
{
    use SoftDeletes;

    protected $table = 'receipts';

    protected $fillable = [
        'receipt_number',
        'user_id',
        'source_type',
        'source_id',
        'transaction_reference',
        'receipt_type',
        'product_name',
        'category',
        'customer_id',
        'customer_name',
        'subtotal',
        'admin_fee',
        'discount',
        'total',
        'payment_method',
        'payment_status',
        'transaction_status',
        'paid_at',
        'email_sent_at',
        'email_status',
        'email_message_id',
    ];

    protected $casts = [
        'user_id' => 'string',
        'subtotal' => 'decimal:2',
        'admin_fee' => 'decimal:2',
        'discount' => 'decimal:2',
        'total' => 'decimal:2',
        'paid_at' => 'datetime',
        'email_sent_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function ppobTransaction()
    {
        if ($this->source_type === 'ppob_transaction') {
            return $this->belongsTo(PpoTransactionModel::class, 'source_id');
        }

        return null;
    }
}
