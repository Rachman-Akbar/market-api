<?php

namespace App\Domains\Catalog\Domain\Entities;

use App\Domains\Catalog\Domain\ValueObjects\ProductStatus;
use DomainException;

class Product
{
    private function __construct(
        private readonly ?string $id,
        private string $sellerId,
        private string $name,
        private string $slug,
        private ?string $description,
        private int $price,
        private string $status,
    ) {}

    /**
     * Untuk create dari business flow
     */
    public static function create(
        string $sellerId,
        string $name,
        string $slug,
        ?string $description,
        int $price,
    ): self {
        return new self(
            null,
            $sellerId,
            $name,
            $slug,
            $description,
            $price,
            'inactive'
        );
    }

    /**
     * ⭐ Untuk load dari database
     */
    public static function rehydrate(
        string $id,
        string $sellerId,
        string $name,
        string $slug,
        ?string $description,
        int $price,
        string $status,
    ): self {
        return new self(
            $id,
            $sellerId,
            $name,
            $slug,
            $description,
            $price,
            $status
        );
    }

    /* ===== GETTERS ===== */

    public function id(): ?string { return $this->id; }
    public function sellerId(): string { return $this->sellerId; }
    public function name(): string { return $this->name; }
    public function slug(): string { return $this->slug; }
    public function description(): ?string { return $this->description; }
    public function price(): int { return $this->price; }
    public function status(): string { return $this->status; }

    public function activate(): void
    {
        $this->status = 'active';
    }

    public function deactivate(): void
    {
        $this->status = 'inactive';
    }
}
