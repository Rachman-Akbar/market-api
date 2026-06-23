<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;
use App\Domains\Catalog\Product\Presentation\Http\Controllers\ProductController;
use App\Domains\Catalog\Product\Presentation\Http\Controllers\ProductAttributeController;
use App\Domains\Catalog\Product\Presentation\Http\Controllers\ProductVariantController;

Route::prefix('products')->name('products.')->group(function () {
    // --- Public Routes ---
    Route::get('/slug/{slug}', [ProductController::class, 'showBySlug'])->name('show-by-slug');
    Route::get('/{id}', [ProductController::class, 'show'])->whereNumber('id')->name('show');
    Route::get('/{productId}/variants', [ProductVariantController::class, 'index'])->whereNumber('productId')->name('variants.index');
    Route::get('/{productId}/variants/{variantId}', [ProductVariantController::class, 'show'])->whereNumber('productId')->whereNumber('variantId')->name('variants.show');

    // --- Protected Routes (Seller Only) ---
    Route::middleware(['auth:sanctum', 'verified.email', 'active.role:seller'])->group(function () {
        Route::get('/', [ProductController::class, 'sellerIndex'])->name('index'); // List produk internal seller
        Route::post('/', [ProductController::class, 'store'])->name('store');
        Route::put('/{id}', [ProductController::class, 'update'])->whereNumber('id')->name('update');
        Route::delete('/{id}', [ProductController::class, 'destroy'])->whereNumber('id')->name('destroy');

        // Product Attributes (Hanya dikelola seller)
        Route::get('/product-attributes', [ProductAttributeController::class, 'index'])->name('product-attributes.index');
        Route::post('/product-attributes', [ProductAttributeController::class, 'store'])->name('product-attributes.store');
        Route::get('/product-attributes/{id}', [ProductAttributeController::class, 'show'])->whereNumber('id')->name('product-attributes.show');
        Route::put('/product-attributes/{id}', [ProductAttributeController::class, 'update'])->whereNumber('id')->name('product-attributes.update');
        Route::delete('/product-attributes/{id}', [ProductAttributeController::class, 'destroy'])->whereNumber('id')->name('product-attributes.destroy');

        // Product Variants Write Ops
        Route::post('/{productId}/variants', [ProductVariantController::class, 'store'])->whereNumber('productId')->name('variants.store');
        Route::put('/{productId}/variants/{variantId}', [ProductVariantController::class, 'update'])->whereNumber('productId')->whereNumber('variantId')->name('variants.update');
        Route::delete('/{productId}/variants/{variantId}', [ProductVariantController::class, 'destroy'])->whereNumber('productId')->whereNumber('variantId')->name('variants.destroy');
    });
});