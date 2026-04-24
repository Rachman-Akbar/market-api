<?php

use Illuminate\Support\Facades\Route;
use App\Domains\Catalog\Presentation\Http\Controllers\ProductController;
use App\Domains\Catalog\Presentation\Http\Controllers\CatalogGroupController;
use App\Domains\Catalog\Presentation\Http\Controllers\CategoryController;
use App\Domains\Catalog\Presentation\Http\Controllers\BannerController;
use App\Domains\Catalog\Presentation\Http\Controllers\StoreController;

Route::prefix('catalog')->group(function () {
    Route::get('products', [ProductController::class, 'index']);

    Route::get('products/{slug}', [ProductController::class, 'showBySlug'])
        ->where('slug', '[A-Za-z0-9\-]+');

    Route::get('products/id/{id}', [ProductController::class, 'show'])
        ->whereNumber('id');

    Route::get('catalog-groups', [CatalogGroupController::class, 'index']);

    Route::get('categories', [CategoryController::class, 'index']);

    Route::get('banners', [BannerController::class, 'index']);

    Route::get('stores', [StoreController::class, 'index']);

    Route::get('stores/{slug}', [StoreController::class, 'showBySlug'])
        ->where('slug', '[A-Za-z0-9\-]+');

    Route::get('stores/{slug}/products', [StoreController::class, 'productsBySlug'])
        ->where('slug', '[A-Za-z0-9\-]+');
});