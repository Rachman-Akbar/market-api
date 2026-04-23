<?php

use Illuminate\Support\Facades\Route;
use App\Domains\Catalog\Presentation\Http\Controllers\ProductController;
use App\Domains\Catalog\Presentation\Http\Controllers\CatalogGroupController;
use App\Domains\Catalog\Presentation\Http\Controllers\CategoryController;
use App\Domains\Catalog\Presentation\Http\Controllers\BannerController;
use App\Domains\Catalog\Presentation\Http\Controllers\StoreController;

Route::prefix('catalog')->group(function () {
    Route::get('products', [ProductController::class, 'index']);
    Route::get('products/{id}', [ProductController::class, 'show']);

    Route::get('catalog-groups', [CatalogGroupController::class, 'index']);

    Route::get('categories', [CategoryController::class, 'index']);

    Route::get('banners', [BannerController::class, 'index']);

    Route::get('stores', [StoreController::class, 'index']);
});