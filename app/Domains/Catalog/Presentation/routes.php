<?php

declare(strict_types=1);

use App\Domains\Catalog\Presentation\Http\Controllers\BannerController;
use App\Domains\Catalog\Presentation\Http\Controllers\CatalogGroupController;
use App\Domains\Catalog\Presentation\Http\Controllers\CategoryController;
use App\Domains\Catalog\Presentation\Http\Controllers\ProductController;
use App\Domains\Catalog\Presentation\Http\Controllers\Seller\SellerProductController;
use App\Domains\Catalog\Presentation\Http\Controllers\Seller\SellerStoreCatalogGroupController;
use App\Domains\Catalog\Presentation\Http\Controllers\Seller\SellerStoreCategoryController;
use App\Http\Middleware\EnsureActiveRole;
use App\Http\Middleware\EnsureApiTokenIsValid;
use Illuminate\Support\Facades\Route;

/**
 * Public / Buyer catalog routes.
 */
Route::prefix('catalog')
    ->name('catalog.')
    ->group(function () {


     Route::get(
            '/header-menu',
            [CategoryController::class, 'headerMenu']
        );

        
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

        Route::prefix('catalog-groups')->group(function () {
    Route::get('/', [CatalogGroupController::class, 'index']);
    Route::post('/', [CatalogGroupController::class, 'store']);
    
    // Route::get('{id}', [CatalogGroupController::class, 'show']);
    // Route::put('{id}', [CatalogGroupController::class, 'update']);
    
       // GET CATEGORIES
    // Route::get('/{id}/categories', [
    //     CatalogGroupController::class,
    //     'categories'
    // ])->whereNumber('id');
    
    // GET BY SLUG
    Route::get('/{slug}', [
        CatalogGroupController::class,
        'showBySlug'
    ]);

    
    // Endpoint penting untuk marketplace
    Route::get('{id}/categories', [CatalogGroupController::class, 'categories']);
});

    Route::prefix('categories')
        ->controller(CategoryController::class)
        ->group(function () {

        /**
         * ALL CATEGORIES
         */
        Route::get('/', 'index');

        /**
         * MENU
         */
        Route::get('/menu', 'menu');

        /**
         * PRODUCTS BY FULL PATH
         *
         * IMPORTANT:
         * MUST BE ABOVE /path/{path}
         */
        Route::get(
            '/path/{path}/products',
            'productsByPath'
        )->where('path', '.*');

        /**
         * CATEGORY BY FULL PATH
         */
        Route::get(
            '/path/{path}',
            'showByPath'
        )->where('path', '.*');

        /**
         * CATEGORY BY SLUG
         */
        Route::get(
            '/slug/{slug}',
            'showBySlug'
        );

        /**
         * PRODUCTS BY SLUG
         */
        Route::get(
            '/{slug}/products',
            'productsBySlug'
        );

        /**
         * CREATE
         */
        Route::post('/', 'store');

        /**
         * UPDATE
         */
        Route::put(
            '/{id}',
            'update'
        )->whereNumber('id');

        /**
         * DELETE
         */
        Route::delete(
            '/{id}',
            'destroy'
        )->whereNumber('id');
    });

    
        Route::prefix('banners')
            ->name('banners.')
            ->group(function () {
                Route::get('/', [BannerController::class, 'index'])
                    ->name('index');
            });
    });

// /**
//  * Seller catalog routes.
//  */
// Route::middleware([
//     'auth:sanctum',
//     'active.role:seller',
// ])
    // ->prefix('seller/catalog')
    // ->name('seller.catalog.')
    // ->group(function () {
    //     Route::apiResource('products', SellerProductController::class);

    //     Route::apiResource(
    //         'store-categories',
    //         SellerStoreCategoryController::class
    //     )->parameters([
    //         'store-categories' => 'storeCategory',
    //     ]);

    //     Route::apiResource(
    //         'store-catalog-groups',
    //         SellerStoreCatalogGroupController::class
    //     )->parameters([
    //         'store-catalog-groups' => 'storeCatalogGroup',
    //     ]);
    // });
