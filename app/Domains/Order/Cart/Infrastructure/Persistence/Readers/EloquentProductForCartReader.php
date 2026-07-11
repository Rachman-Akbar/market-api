<?php

declare(strict_types=1);

namespace App\Domains\Order\Cart\Infrastructure\Persistence\Readers;

use App\Domains\Order\Cart\Application\Readers\ProductForCartReaderInterface;
use App\Domains\Order\Cart\Domain\ValueObjects\Money;
use App\Domains\Order\Cart\Domain\ValueObjects\VariantDetails;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

final class EloquentProductForCartReader implements ProductForCartReaderInterface
{
    public function getVariantStock(int $productVariantId): ?int
    {
        $stock = DB::table('product_variants')->where('id', $productVariantId)->value('stock');
        return $stock === null ? null : (int) $stock;
    }

    public function getVariantDetails(int $productVariantId): ?VariantDetails
    {
        $select = [
            'product_variants.id',
            'product_variants.product_id',
            'product_variants.name as variant_name',
            'product_variants.store_id',
            'product_variants.sku',
            'product_variants.price',
            'product_variants.stock',
            'products.name as product_name',
            'products.thumbnail',
            'stores.name as store_name',
        ];

        $weightColumn = null;
        foreach (['weight_gram', 'weight', 'berat_gram', 'berat'] as $candidate) {
            if (Schema::hasColumn('products', $candidate)) {
                $weightColumn = $candidate;
                $select[] = "products.{$candidate} as product_weight";
                break;
            }
        }

        $variant = DB::table('product_variants')
            ->join('products', 'products.id', '=', 'product_variants.product_id')
            ->join('stores', 'stores.id', '=', 'product_variants.store_id')
            ->select($select)
            ->where('product_variants.id', $productVariantId)
            ->first();

        if (!$variant) {
            return null;
        }

        $attributes = DB::table('product_variant_values')
            ->join('product_attributes', 'product_variant_values.attribute_id', '=', 'product_attributes.id')
            ->where('product_variant_values.variant_id', $productVariantId)
            ->pluck('product_variant_values.value', 'product_attributes.name')
            ->toArray();

        $rawWeight = $weightColumn ? (float) ($variant->product_weight ?? 0) : 0;
        $weight = $rawWeight > 0 ? (int) ceil($rawWeight) : 1000;

        return new VariantDetails(
            id: (int) $variant->id,
            productId: (int) $variant->product_id,
            name: (string) ($variant->variant_name ?: $variant->product_name),
            productName: (string) $variant->product_name,
            storeId: (int) $variant->store_id,
            storeName: (string) $variant->store_name,
            sku: (string) $variant->sku,
            price: new Money((int) $variant->price),
            stock: (int) $variant->stock,
            weight: max(1, $weight),
            thumbnail: $variant->thumbnail ? (string) $variant->thumbnail : null,
            attributes: $attributes
        );
    }
}
