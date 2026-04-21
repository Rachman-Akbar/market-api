<?php

namespace App\Domains\Catalog\Domain\Entities;

final class Category
{
    public function __construct(
        private string $id,
        private string $catalogGroupId,
        private string $name,
        private string $slug,
        private ?string $description
    ) {}

    public function id(): string
    {
        return $this->id;
    }

    public function catalogGroupId(): string
    {
        return $this->catalogGroupId;
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

    // === DOMAIN BEHAVIOR ===

    public function rename(string $name): void
    {
        $this->name = $name;
    }

    public function changeDescription(?string $description): void
    {
        $this->description = $description;
    }
}