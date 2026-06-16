<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;
use App\Domains\Catalog\Product\Presentation\Http\Controllers\ProductController;
use App\Domains\Catalog\Product\Presentation\Http\Controllers\ProductAttributeController;
use App\Domains\Catalog\Product\Presentation\Http\Controllers\ProductVariantController;

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
