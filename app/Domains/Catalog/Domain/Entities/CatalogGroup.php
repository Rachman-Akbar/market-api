<?php

namespace App\Domains\Catalog\Domain\Entities;

class CatalogGroup
{
    private ?string $id;
    private string $name;
    private string $slug;
    private ?string $description;

    public function __construct(
        ?string $id,
        string $name,
        string $slug,
        ?string $description = null
    ) {
        $this->id = $id;
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
            throw new \DomainException('Catalog Group name too short');
        }

        $this->name = $name;
    }

    public function changeSlug(string $slug): void
    {
        if (empty($slug)) {
            throw new \DomainException('Slug cannot be empty');
        }

        $this->slug = $slug;
    }
}
