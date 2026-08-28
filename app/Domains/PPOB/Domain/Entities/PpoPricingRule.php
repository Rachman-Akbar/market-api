<?php

declare(strict_types=1);

namespace App\Domains\PPOB\Domain\Entities;

final class PpoPricingRule
{
    public function __construct(
        public ?int $id,
        public string $level, // global | category | operator | product
        public ?string $category,
        public ?int $operatorId,
        public ?int $productId,
        public string $marginType, // fixed | percentage
        public float $marginValue,
        public string $adminFeeType, // fixed | percentage
        public float $adminFeeValue,
        public string $commissionType, // fixed | percentage
        public float $commissionValue,
        public ?float $minSellingPrice,
        public ?float $maxSellingPrice,
        public int $priority,
        public bool $isActive = true,
        public ?string $description = null,
        public ?string $createdAt = null,
        public ?string $updatedAt = null,
    ) {}

    public function applyTo(PpoProduct $product): PpoProduct
    {
        $providerPrice = $product->providerPrice;

        $margin = $this->resolveValue($this->marginType, $this->marginValue, $providerPrice);
        $adminFee = $this->resolveValue($this->adminFeeType, $this->adminFeeValue, $providerPrice);
        $commission = $this->resolveValue($this->commissionType, $this->commissionValue, $providerPrice);

        $sellingPrice = round($providerPrice + $margin + $adminFee, 2);

        if ($this->minSellingPrice !== null && $sellingPrice < $this->minSellingPrice) {
            $sellingPrice = (float) $this->minSellingPrice;
        }

        if ($this->maxSellingPrice !== null && $sellingPrice > $this->maxSellingPrice) {
            $sellingPrice = (float) $this->maxSellingPrice;
        }

        $netProfit = round($sellingPrice - $providerPrice, 2);

        return new PpoProduct(
            id: $product->id,
            operatorId: $product->operatorId,
            category: $product->category,
            productType: $product->productType,
            providerProductCode: $product->providerProductCode,
            name: $product->name,
            brand: $product->brand,
            nominal: $product->nominal,
            providerPrice: $providerPrice,
            adminFee: $adminFee,
            commission: $commission,
            margin: $margin,
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

    private function resolveValue(string $type, float $value, float $base): float
    {
        return $type === 'percentage'
            ? round($base * $value / 100, 2)
            : $value;
    }
}
