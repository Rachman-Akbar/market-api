<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;
use App\Domains\Catalog\Presentation\Http\Controllers\ProductController;
use App\Domains\Catalog\Presentation\Http\Controllers\CatalogGroupController;
use App\Domains\Catalog\Presentation\Http\Controllers\CategoryController;
use App\Domains\Catalog\Presentation\Http\Controllers\BannerController;

Route::prefix('catalog')
    ->name('catalog.')
    ->group(function () {

    Route::prefix('products')
    ->name('products.')
    ->group(function () {
        Route::get('/', [ProductController::class, 'index'])
            ->name('index');

        Route::get('/id/{id}', [ProductController::class, 'show'])
            ->whereNumber('id')
            ->name('show');

        Route::get('/{slug}', [ProductController::class, 'showBySlug'])
            ->where('slug', '[A-Za-z0-9\-]+')
            ->name('show-by-slug');
    });
    
    Route::prefix('catalog-groups')
            ->name('catalog-groups.')
            ->group(function () {
                Route::get('/', [CatalogGroupController::class, 'index'])
                    ->name('index');
            });

Route::prefix('categories')
    ->name('categories.')
    ->group(function () {
        Route::get('/', [CategoryController::class, 'index'])
            ->name('index');

        Route::get('/menu', [CategoryController::class, 'menu'])
            ->name('menu');

        Route::get('/{slug}/products', [CategoryController::class, 'productsBySlug'])
            ->where('slug', '[A-Za-z0-9\-]+')
            ->name('products');

        Route::get('/{slug}', [CategoryController::class, 'showBySlug'])
            ->where('slug', '[A-Za-z0-9\-]+')
            ->name('show-by-slug');
    });

            Route::prefix('banners')
            ->name('banners.')
            ->group(function () {
                Route::get('/', [BannerController::class, 'index'])
                    ->name('index');
            });
    });
