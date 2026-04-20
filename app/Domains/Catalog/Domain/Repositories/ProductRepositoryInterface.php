<?php

namespace App\Domains\Catalog\Domain\Repositories;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;

interface ProductRepositoryInterface
{
    /**
     * Ambil daftar product (catalog listing)
     */
    public function paginate(array $filters = []): LengthAwarePaginator;

    /**
     * Ambil 1 product berdasarkan ID
     */
    public function findById(string $id);

    /**
     * Ambil product berdasarkan slug
     */
    public function findBySlug(string $slug);

    /**
     * Buat product baru
     */
    public function create(array $data);

    /**
     * Update product
     */
    public function update(string $id, array $data);

    /**
     * Delete product
     */
    public function delete(string $id): bool;
}