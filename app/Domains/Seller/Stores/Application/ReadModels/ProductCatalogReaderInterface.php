<?php

declare(strict_types=1);

namespace App\Domains\Seller\Stores\Application\ReadModels;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;

interface ProductCatalogReaderInterface
{
    /**
     * Mengambil daftar produk aktif & published milik toko tertentu berdasarkan slug toko.
     */
    public function publishedProductsByStoreSlug(string $slug, array $filters = []): LengthAwarePaginator;
}
