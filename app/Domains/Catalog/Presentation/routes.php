<?php

use App\Domains\Catalog\Presentation\Http\Controllers\BannerController;
use Illuminate\Support\Facades\Route;
use App\Domains\Catalog\Presentation\Http\Controllers\ProductController;
use App\Domains\Catalog\Presentation\Http\Controllers\CatalogGroupController;
use App\Domains\Catalog\Presentation\Http\Controllers\CategoryController;

Route::prefix('catalog')->group(function () {

    Route::get('products', [ProductController::class, 'index']);
    Route::get('products/{id}', [ProductController::class, 'show']);

    Route::get('catalog-groups', [CatalogGroupController::class, 'index']);
    Route::post('catalog-groups', [CatalogGroupController::class, 'store']);
    Route::put('catalog-groups/{id}', [CatalogGroupController::class, 'update']);

    Route::get('categories', [CategoryController::class, 'index']);
    Route::post('categories', [CategoryController::class, 'store']);

    Route::get('banners', [BannerController::class, 'index']);

});
