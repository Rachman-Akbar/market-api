<?php

declare(strict_types=1);

namespace App\Domains\Order\Cart\Infrastructure\Persistence\Readers;

use App\Domains\Order\Cart\Application\Readers\ProductForCartReaderInterface;
use App\Domains\Order\Cart\Domain\ValueObjects\VariantDetails;
use App\Domains\Order\Cart\Domain\ValueObjects\Money;
use Illuminate\Support\Facades\DB;

final class EloquentProductForCartReader implements ProductForCartReaderInterface
{
    public function getVariantStock(int $productVariantId): ?int
    {
        // Gunakan find() atau where()->first() dan pastikan datanya ada
        $variant = DB::table('product_variants')
            ->where('id', $productVariantId)
            ->first();

        // Jika data tidak ditemukan di DB, ia akan melempar null ke UseCase
        if (!$variant) {
            return null;
        }

        // Paksa konversi ke integer untuk mengantisipasi string dari database
        return isset($variant->stock) ? (int) $variant->stock : 0;
    }

public function getVariantDetails(int $productVariantId): ?VariantDetails
{
    $variant = DB::table('product_variants')
        ->where('id', $productVariantId)
        ->first();

    if (!$variant) {
        return null;
    }

    // REVISI: Sesuaikan nama tabel dan kolom sesuai dengan database asli (HeidiSQL)
    $attributes = DB::table('product_variant_values') // <-- Ubah dari product_variant_attribute_values
        ->join('product_attributes', 'product_variant_values.attribute_id', '=', 'product_attributes.id') // <-- Ubah dari attributes
        ->where('product_variant_values.variant_id', $productVariantId) // <-- Kolomnya adalah variant_id, bukan product_variant_id
        ->pluck('product_variant_values.value', 'product_attributes.name')
        ->toArray();

    return new VariantDetails(
    id: (int) $variant->id,
    productId: (int) $variant->product_id, // Ambil dari kolom product_id di tabel variants
    name: (string) $variant->name,
    storeId: (int) $variant->store_id,
    sku: (string) $variant->sku,
    price: new Money((int) $variant->price),
    attributes: $attributes
);
}

}
