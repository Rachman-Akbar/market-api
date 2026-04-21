<?php

namespace App\Domains\Catalog\Application\UseCases\Product;

use App\Domains\Catalog\Domain\Repositories\ProductRepositoryInterface;
use App\Domains\Catalog\Domain\Entities\Product;
use Illuminate\Support\Str;

class CreateProductUseCase
{
    public function __construct(
        private ProductRepositoryInterface $repository
    ) {}

    public function execute(array $data): Product
    {
        $data['slug'] = Str::slug($data['name']);
        $data['status'] ??= 'inactive';

        return $this->repository->create($data);
    }
}
