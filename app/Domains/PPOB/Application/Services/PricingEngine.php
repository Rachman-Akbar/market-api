<?php

declare(strict_types=1);

namespace App\Domains\PPOB\Application\Services;

use App\Domains\PPOB\Domain\Entities\PpoProduct;
use App\Domains\PPOB\Domain\Entities\PpoPricingRule;
use App\Domains\PPOB\Domain\Repositories\PpoPricingRuleRepositoryInterface;
use Illuminate\Support\Facades\Cache;

/**
 * Central pricing engine. All PPOB selling prices are derived from a single
 * calculation path (provider cost + configured margin/admin fee/commission).
 * No price is ever hardcoded.
 *
 * Only scalar arrays are cached (the database cache store cannot safely
 * unserialize arbitrary DTO objects => __PHP_Incomplete_Class), so entities
 * are reconstructed from the cached breakdown on a cache hit.
 */
class PricingEngine
{
    private const CACHE_TTL = 900;

    public function __construct(
        private PpoPricingRuleRepositoryInterface $pricingRules,
    ) {}

    /**
     * Resolve the applied price components for a product.
     *
     * @return array{product: PpoProduct, rule: ?PpoPricingRule}
     */
    public function priceProduct(PpoProduct $product): array
    {
        $key = "ppob_price_product_{$product->id}";

        $cached = Cache::remember($key, self::CACHE_TTL, function () use ($product) {
            $rule = $this->pricingRules->findMostSpecific(
                $product->id,
                $product->operatorId,
                $product->category,
            );

            $priced = $rule ? $rule->applyTo($product) : $this->passThrough($product);

            // Cache only scalars; reconstruct the entity from this array later.
            return [
                'product' => $this->toArray($priced),
                'rule' => $rule ? $this->toRuleArray($rule) : null,
            ];
        });

        return [
            'product' => $this->fromArray($product, $cached['product']),
            'rule' => $cached['rule'] ? $this->fromRuleArray($cached['rule']) : null,
        ];
    }

    /**
     * No rule configured => use the admin-managed price fields stored on the
     * product as-is (margin/admin_fee/commission already set by admin).
     */
    private function passThrough(PpoProduct $product): PpoProduct
    {
        $sellingPrice = round($product->providerPrice + $product->margin + $product->adminFee, 2);

        return new PpoProduct(
            id: $product->id,
            operatorId: $product->operatorId,
            category: $product->category,
            productType: $product->productType,
            providerProductCode: $product->providerProductCode,
            name: $product->name,
            brand: $product->brand,
            nominal: $product->nominal,
            providerPrice: $product->providerPrice,
            adminFee: $product->adminFee,
            commission: $product->commission,
            margin: $product->margin,
            sellingPrice: $sellingPrice,
            status: $product->status,
            isAvailable: $product->isAvailable,
            isActive: $product->isActive,
            iconUrl: $product->iconUrl,
            metadata: $product->metadata,
            createdAt: $product->createdAt,
            updatedAt: $product->updatedAt,
        );
    }

    /**
     * Break down a product into the finance components for a transaction.
     *
     * @return array{provider_price: float, admin_fee: float, commission: float, margin: float, revenue: float, net_profit: float, total_amount: float, selling_price: float}
     */
    public function buildBreakdown(PpoProduct $priced): array
    {
        $revenue = $priced->sellingPrice;
        $providerPrice = $priced->providerPrice;
        $netProfit = round($revenue - $providerPrice, 2);

        return [
            'provider_price' => $providerPrice,
            'admin_fee' => $priced->adminFee,
            'commission' => $priced->commission,
            'margin' => $priced->margin,
            'revenue' => $revenue,
            'net_profit' => $netProfit,
            'total_amount' => $revenue,
            'selling_price' => $revenue,
        ];
    }

    public function clearProductCache(int $productId): void
    {
        Cache::forget("ppob_price_product_{$productId}");
    }

    private function toArray(PpoProduct $p): array
    {
        return [
            'id' => $p->id,
            'operator_id' => $p->operatorId,
            'category' => $p->category,
            'product_type' => $p->productType,
            'provider_product_code' => $p->providerProductCode,
            'name' => $p->name,
            'brand' => $p->brand,
            'nominal' => $p->nominal,
            'provider_price' => $p->providerPrice,
            'admin_fee' => $p->adminFee,
            'commission' => $p->commission,
            'margin' => $p->margin,
            'selling_price' => $p->sellingPrice,
            'status' => $p->status,
            'is_available' => $p->isAvailable,
            'is_active' => $p->isActive,
            'icon_url' => $p->iconUrl,
            'metadata' => $p->metadata,
            'created_at' => $p->createdAt,
            'updated_at' => $p->updatedAt,
        ];
    }

    private function fromArray(PpoProduct $source, array $a): PpoProduct
    {
        return new PpoProduct(
            id: $a['id'] ?? $source->id,
            operatorId: $a['operator_id'] ?? $source->operatorId,
            category: $a['category'] ?? $source->category,
            productType: $a['product_type'] ?? $source->productType,
            providerProductCode: $a['provider_product_code'] ?? $source->providerProductCode,
            name: $a['name'] ?? $source->name,
            brand: $a['brand'] ?? $source->brand,
            nominal: $a['nominal'] ?? $source->nominal,
            providerPrice: (float) ($a['provider_price'] ?? $source->providerPrice),
            adminFee: (float) ($a['admin_fee'] ?? $source->adminFee),
            commission: (float) ($a['commission'] ?? $source->commission),
            margin: (float) ($a['margin'] ?? $source->margin),
            sellingPrice: (float) ($a['selling_price'] ?? $source->sellingPrice),
            status: $a['status'] ?? $source->status,
            isAvailable: (bool) ($a['is_available'] ?? $source->isAvailable),
            isActive: (bool) ($a['is_active'] ?? $source->isActive),
            iconUrl: $a['icon_url'] ?? $source->iconUrl,
            metadata: $a['metadata'] ?? $source->metadata,
            createdAt: $a['created_at'] ?? $source->createdAt,
            updatedAt: $a['updated_at'] ?? $source->updatedAt,
        );
    }

    private function toRuleArray(PpoPricingRule $r): array
    {
        return [
            'id' => $r->id,
            'level' => $r->level,
            'category' => $r->category,
            'operator_id' => $r->operatorId,
            'product_id' => $r->productId,
            'margin_type' => $r->marginType,
            'margin_value' => $r->marginValue,
            'admin_fee_type' => $r->adminFeeType,
            'admin_fee_value' => $r->adminFeeValue,
            'commission_type' => $r->commissionType,
            'commission_value' => $r->commissionValue,
            'min_selling_price' => $r->minSellingPrice,
            'max_selling_price' => $r->maxSellingPrice,
            'priority' => $r->priority,
            'is_active' => $r->isActive,
            'description' => $r->description,
            'created_at' => $r->createdAt,
            'updated_at' => $r->updatedAt,
        ];
    }

    private function fromRuleArray(array $a): PpoPricingRule
    {
        return new PpoPricingRule(
            id: $a['id'],
            level: $a['level'],
            category: $a['category'],
            operatorId: $a['operator_id'],
            productId: $a['product_id'],
            marginType: $a['margin_type'],
            marginValue: (float) $a['margin_value'],
            adminFeeType: $a['admin_fee_type'],
            adminFeeValue: (float) $a['admin_fee_value'],
            commissionType: $a['commission_type'],
            commissionValue: (float) $a['commission_value'],
            minSellingPrice: $a['min_selling_price'] !== null ? (float) $a['min_selling_price'] : null,
            maxSellingPrice: $a['max_selling_price'] !== null ? (float) $a['max_selling_price'] : null,
            priority: (int) $a['priority'],
            isActive: (bool) $a['is_active'],
            description: $a['description'],
            createdAt: $a['created_at'],
            updatedAt: $a['updated_at'],
        );
    }
}
