<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;

use App\Domains\Catalog\Presentation\Http\Controllers\BannerController;
use App\Domains\Catalog\Presentation\Http\Controllers\CatalogGroupController;
use App\Domains\Catalog\Presentation\Http\Controllers\CategoryController;
use App\Domains\Catalog\Presentation\Http\Controllers\ProductController;
use App\Domains\Catalog\Presentation\Http\Controllers\ProductAttributeController;
use App\Domains\Catalog\Presentation\Http\Controllers\ProductVariantController;

Route::prefix('catalog')
    ->name('catalog.')
    ->group(function () {

        /*
        |--------------------------------------------------------------------------
        | HEADER MENU
        |--------------------------------------------------------------------------
        */
        Route::get(
            '/header-menu',
            [CategoryController::class, 'headerMenu']
        )->name('header-menu');

        /*
        |--------------------------------------------------------------------------
        | PRODUCT ATTRIBUTES
        |--------------------------------------------------------------------------
        */
        Route::prefix('attributes')
            ->name('attributes.')
            ->controller(ProductAttributeController::class)
            ->group(function () {

                Route::get('/', 'index')
                    ->name('index');

                Route::get('/{id}', 'show')
                    ->whereNumber('id')
                    ->name('show');

                // nanti jika sudah siap CRUD

                // Route::post('/', 'store')
                //     ->name('store');

                // Route::put('/{id}', 'update')
                //     ->whereNumber('id')
                //     ->name('update');

                // Route::delete('/{id}', 'destroy')
                //     ->whereNumber('id')
                //     ->name('destroy');

        });
        /*
        |--------------------------------------------------------------------------
        | PRODUCTS
        |--------------------------------------------------------------------------
        */
        Route::prefix('products')
            ->name('products.')
            ->controller(ProductController::class)
            ->group(function () {

                Route::get('/', 'index')
                    ->name('index');

                Route::post('/', 'store')
                    ->name('store');

                Route::get('/id/{id}', 'show')
                    ->whereNumber('id')
                    ->name('show');

                Route::put('/{id}', 'update')
                    ->whereNumber('id')
                    ->name('update');

                Route::delete('/{id}', 'destroy')
                    ->whereNumber('id')
                    ->name('destroy');

                Route::prefix('{productId}/variants')
                        ->name('variants.')
                        ->controller(ProductVariantController::class)
                        ->group(function () {

                            Route::get('/', 'index');

                            Route::get(
                                '/{variantId}', 'show');

                            Route::post('/', 'store');

                            Route::put(
                                '/{variantId}',
                                'update'
                            );

                            Route::delete(
                                '/{variantId}',
                                'destroy'
                            );
                        });

                Route::get('/{slug}', 'showBySlug')
                    ->where('slug', '[A-Za-z0-9\-]+')
                    ->name('show-by-slug');

            });

        /*
        |--------------------------------------------------------------------------
        | CATALOG GROUPS
        |--------------------------------------------------------------------------
        */
        Route::prefix('catalog-groups')
            ->name('catalog-groups.')
            ->group(function () {

                Route::get('/', [
                    CatalogGroupController::class,
                    'index'
                ]);

                Route::post('/', [
                    CatalogGroupController::class,
                    'store'
                ]);

                Route::get('/{id}', [CatalogGroupController::class, 'show'])->whereNumber('id');


                Route::get('/{id}/categories', [
                    CatalogGroupController::class,
                    'categories'
                    ])->whereNumber('id');

                Route::get('/{slug}', [
                        CatalogGroupController::class,
                        'showBySlug'
                    ]);

          });

        /*
        |--------------------------------------------------------------------------
        | CATEGORIES
        |--------------------------------------------------------------------------
        */
        Route::prefix('categories')
            ->controller(CategoryController::class)
            ->group(function () {

                Route::get('/', 'index');

                Route::get('/menu', 'menu');

                Route::get(
                    '/path/{path}/products',
                    'productsByPath'
                )->where('path', '.*');

                Route::get(
                    '/path/{path}',
                    'showByPath'
                )->where('path', '.*');

                Route::get(
                    '/slug/{slug}',
                    'showBySlug'
                );

                Route::get(
                    '/{slug}/products',
                    'productsBySlug'
                );

                Route::post('/', 'store');

                Route::put(
                    '/{id}',
                    'update'
                )->whereNumber('id');

                Route::delete(
                    '/{id}',
                    'destroy'
                )->whereNumber('id');
            });

        /*
        |--------------------------------------------------------------------------
        | BANNERS
        |--------------------------------------------------------------------------
        */
        Route::prefix('banners')
            ->name('banners.')
            ->group(function () {

                Route::get(
                    '/',
                    [BannerController::class, 'index']
                )->name('index');
            });


    });
