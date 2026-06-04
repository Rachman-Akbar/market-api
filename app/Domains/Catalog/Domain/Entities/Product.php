<?php

declare(strict_types=1);

namespace App\Domains\Catalog\Domain\Entities;

final class Product
{
    public function __construct(
        private ?int $id,
        private ?int $storeId,
        private ?int $primaryCategoryId,
        private array $categoryIds,
        private string $sellerId,
        private string $name,
        private string $slug,
        private ?string $description,
        private ?string $thumbnail,
        private string $status,
        private ?Category $primaryCategory = null,
        private array $categories = [],
        private array $images = [],
        private array $variants = [], // Ditambahkan: Menampung array of ProductVariant entities
    ) {}

    public function id(): ?int { return $this->id; }
    public function storeId(): ?int { return $this->storeId; }
    public function primaryCategoryId(): ?int { return $this->primaryCategoryId; }
    public function categoryId(): ?int { return $this->primaryCategoryId; }
    public function categoryIds(): array { return $this->categoryIds; }
    public function sellerId(): string { return $this->sellerId; }
    public function name(): string { return $this->name; }
    public function slug(): string { return $this->slug; }
    public function description(): ?string { return $this->description; }
    public function thumbnail(): ?string { return $this->thumbnail; }
    public function status(): string { return $this->status; }
    public function primaryCategory(): ?Category { return $this->primaryCategory; }
    public function category(): ?Category { return $this->primaryCategory; }
    public function categories(): array { return $this->categories; }
    public function images(): array { return $this->images; }
    public function variants(): array { return $this->variants; }

    /**
     * DInamis: Mengambil harga dari varian default (atau varian pertama jika default kosong)
     */
    public function price(): float
    {
        if (empty($this->variants)) {
            return 0.00;
        }

        foreach ($this->variants as $variant) {
            if ($variant->isDefault()) {
                return $variant->price();
            }
        }

        return $this->variants[0]->price();
    }

    /**
     * Dinamis: Akumulasi total stok dari seluruh varian produk
     */
    public function stock(): int
    {
        $totalStock = 0;
        foreach ($this->variants as $variant) {
            $totalStock += $variant->stock();
        }
        return $totalStock;
    }

    public function rename(string $name): void { $this->name = $name; }
    public function changeSlug(string $slug): void { $this->slug = $slug; }
    public function changeDescription(?string $description): void { $this->description = $description; }
    public function changeThumbnail(?string $thumbnail): void { $this->thumbnail = $thumbnail; }
    public function changeStatus(string $status): void { $this->status = $status; }
    public function assignStore(?int $storeId): void { $this->storeId = $storeId; }
    public function assignPrimaryCategory(?int $primaryCategoryId): void { $this->primaryCategoryId = $primaryCategoryId; }
    public function assignCategory(?int $categoryId): void { $this->assignPrimaryCategory($categoryId); }

    public function assignCategories(array $categoryIds): void
    {
        $this->categoryIds = array_values(array_unique($categoryIds));
    }

    public function assignVariants(array $variants): void
    {
        $this->variants = $variants;
    }
}
