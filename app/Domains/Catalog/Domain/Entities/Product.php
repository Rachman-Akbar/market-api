<?php

declare(strict_types=1);

namespace App\Domains\Catalog\Domain\Entities;

final class Product
{
    public function __construct(
        private ?int $id,
        private ?int $storeId,
        private ?int $categoryId,
        private string $sellerId,
        private string $name,
        private string $slug,
        private ?string $description,
        private float $price,
        private int $stock,
        private ?string $thumbnail,
        private string $status,
        private ?Category $category = null,
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

    public function categoryId(): ?int
    {
        return $this->categoryId;
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

    public function category(): ?Category
    {
        return $this->category;
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

    public function assignCategory(?int $categoryId): void
    {
        $this->categoryId = $categoryId;
    }
}
