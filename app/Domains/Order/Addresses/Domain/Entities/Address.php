<?php

namespace App\Domains\Order\Addresses\Domain\Entities;

use Illuminate\Database\Eloquent\Model;
use InvalidArgumentException;

class Address extends Model
{
    protected $table = 'addresses';

    protected $fillable = [
        'user_id',
        'store_id',
        'label',
        'recipient_name',
        'phone_number',
        'full_address',
        'city',
        'postal_code',
        'latitude',  // <-- Tambahkan ini
        'longitude',
        'is_primary',
    ];

    protected $casts = [
        'is_primary' => 'boolean',
    ];

    /**
     * Domain Validation: Memastikan invariant (aturan bisnis) terpenuhi.
     * Alamat harus memiliki salah satu pemilik (user atau store), tidak boleh keduanya atau kosong.
     */
    protected static function booted()
    {
        static::saving(function (Address $address) {
            if (empty($address->user_id) && empty($address->store_id)) {
                throw new InvalidArgumentException("Alamat harus dikaitkan dengan user_id atau store_id.");
            }
            if (!empty($address->user_id) && !empty($address->store_id)) {
                throw new InvalidArgumentException("Alamat tidak boleh memiliki user_id dan store_id sekaligus.");
            }
        });
    }

    /**
     * Domain Logic: Menandai alamat ini sebagai utama berdasarkan scope pemiliknya.
     */
    public function markAsPrimary(): void
    {
        $query = static::where('id', '!=', $this->id);

        if ($this->user_id) {
            $query->where('user_id', $this->user_id);
        } else {
            $query->where('store_id', $this->store_id);
        }

        $query->update(['is_primary' => false]);
        $this->update(['is_primary' => true]);
    }
}
