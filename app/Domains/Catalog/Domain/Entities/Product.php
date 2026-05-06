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
        private float $price,
        private int $stock,
        private ?string $thumbnail,
        private string $status,
        private ?Category $primaryCategory = null,
        private array $categories = [],
        private array $images = [],
    ) {}

    public function id(): ?int
    {
        return $this->id;
    }

    public function storeId(): ?int
    {
        return $this->storeId;
    }

    public function primaryCategoryId(): ?int
    {
        return $this->primaryCategoryId;
    }

    /**
     * Alias sementara agar kode lama yang masih memanggil categoryId()
     * tidak langsung rusak.
     */
    public function categoryId(): ?int
    {
        return $this->primaryCategoryId;
    }

    public function categoryIds(): array
    {
        return $this->categoryIds;
    }

    public function sellerId(): string
    {
        return $this->sellerId;
    }

    public function name(): string
    {
        return $this->name;
    }

    public function slug(): string
    {
        return $this->slug;
    }

    public function description(): ?string
    {
        return $this->description;
    }

    public function price(): float
    {
        return $this->price;
    }

    public function stock(): int
    {
        return $this->stock;
    }

    public function thumbnail(): ?string
    {
        return $this->thumbnail;
    }

    public function status(): string
    {
        return $this->status;
    }

    public function primaryCategory(): ?Category
    {
        return $this->primaryCategory;
    }

    /**
     * Alias sementara untuk kompatibilitas resource lama.
     */
    public function category(): ?Category
    {
        return $this->primaryCategory;
    }

    public function categories(): array
    {
        return $this->categories;
    }

    public function images(): array
    {
        return $this->images;
    }

    public function rename(string $name): void
    {
        $this->name = $name;
    }

    public function changeSlug(string $slug): void
    {
        $this->slug = $slug;
    }

    public function changeDescription(?string $description): void
    {
        $this->description = $description;
    }

    public function changePrice(float $price): void
    {
        $this->price = $price;
    }

    public function changeStock(int $stock): void
    {
        $this->stock = $stock;
    }

    public function changeThumbnail(?string $thumbnail): void
    {
        $this->thumbnail = $thumbnail;
    }

    public function changeStatus(string $status): void
    {
        $this->status = $status;
    }

    public function assignStore(?int $storeId): void
    {
        $this->storeId = $storeId;
    }

    public function assignPrimaryCategory(?int $primaryCategoryId): void
    {
        $this->primaryCategoryId = $primaryCategoryId;
    }

    /**
     * Alias sementara agar use case lama yang memanggil assignCategory()
     * masih jalan.
     */
    public function assignCategory(?int $categoryId): void
    {
        $this->assignPrimaryCategory($categoryId);
    }

    public function assignCategories(array $categoryIds): void
    {
        $this->categoryIds = array_values(array_unique($categoryIds));
    }
}
