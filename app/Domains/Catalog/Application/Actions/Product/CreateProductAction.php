<?php

namespace App\Domains\Catalog\Application\Actions\Product;

use App\Domains\Catalog\Domain\Repositories\ProductRepositoryInterface;
use Illuminate\Support\Str;

class CreateProductAction
{
    public function __construct(
        private ProductRepositoryInterface $repository
    ) {}

    public function handle($dto)
    {
        $data = (array) $dto;

        $data['slug'] =
            Str::slug($data['name']) . '-' . Str::random(5);

        return $this->repository->create($data);
    }
}