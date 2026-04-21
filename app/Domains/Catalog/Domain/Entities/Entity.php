<?php

namespace App\Domains\Catalog\Domain\Entities;

class Category
{
    private ?string $id;
    private string $entityId;
    private string $name;
    private string $slug;
    private ?string $description;

    public function __construct(
        ?string $id,
        string $entityId,
        string $name,
        string $slug,
        ?string $description = null
    ) {
        $this->id = $id;
        $this->entityId = $entityId;
        $this->name = $name;
        $this->slug = $slug;
        $this->description = $description;
    }

    /* =======================
        GETTERS
    ======================= */

    public function id(): ?string
    {
        return $this->id;
    }

    public function entityId(): string
    {
        return $this->entityId;
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

    /* =======================
        BUSINESS BEHAVIOR
    ======================= */

    public function rename(string $name): void
    {
        if (strlen($name) < 3) {
            throw new \DomainException('Category name too short');
        }

        $this->name = $name;
    }

    public function changeSlug(string $slug): void
    {
        $this->slug = $slug;
    }
}
