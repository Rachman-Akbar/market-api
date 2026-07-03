<?php

namespace App\Domains\Order\Voucher\Domain\Entities;

use Illuminate\Database\Eloquent\Model;
use InvalidArgumentException;

class Voucher extends Model
{
    protected $table = 'vouchers';

    protected $fillable = [
        'code',
        'name',
        'discount_type',
        'discount_value',
        'min_spend',
        'max_discount',
        'starts_at',
        'ends_at',
        'is_active',
        'usage_limit',
        'used_count',
        'store_id'
    ];

    protected $casts = [
        'discount_value' => 'float',
        'min_spend'      => 'float',
        'max_discount'   => 'float',
        'is_active'      => 'boolean',
        'usage_limit'    => 'integer',
        'used_count'     => 'integer',
        'starts_at'      => 'datetime',
        'ends_at'        => 'datetime',
    ];

    protected static function booted()
    {
        static::saving(function (Voucher $voucher) {
            // Validasi Bisnis: Nilai diskon tidak boleh minus
            if ($voucher->discount_value <= 0) {
                throw new InvalidArgumentException("Nilai diskon harus lebih besar dari 0.");
            }

            // Validasi Bisnis: Persentase diskon tidak boleh lebih dari 100%
            if ($voucher->discount_type === 'percentage' && $voucher->discount_value > 100) {
                throw new InvalidArgumentException("Diskon persentase tidak boleh melebihi 100%.");
            }

            // Validasi Bisnis: Tanggal berakhir harus setelah tanggal mulai
            if ($voucher->ends_at->isBefore($voucher->starts_at)) {
                throw new InvalidArgumentException("Tanggal berakhir (ends_at) harus setelah tanggal mulai (starts_at).");
            }
        });
    }
}
