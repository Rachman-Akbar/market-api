<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;
use App\Domains\Catalog\Product\Presentation\Http\Controllers\ProductController;
use App\Domains\Catalog\Product\Presentation\Http\Controllers\ProductAttributeController;
use App\Domains\Catalog\Product\Presentation\Http\Controllers\ProductVariantController;

// =========================================================================
// 1. PUBLIC ROUTES (Tanpa Middleware Auth)
// =========================================================================
Route::prefix('products')->name('products.')->group(function () {
    Route::get('/', [ProductController::class, 'index'])->name('index'); // Marketplace feed
    Route::get('/slug/{slug}', [ProductController::class, 'showBySlug'])->name('show-by-slug');
    Route::get('/{id}', [ProductController::class, 'show'])->whereNumber('id')->name('show');
    
    // Publik melihat varian
    Route::get('/{productId}/variants', [ProductVariantController::class, 'publicIndex'])
         ->whereNumber('productId')
         ->name('variants.public-index');
});

// =========================================================================
// 2. PROTECTED ROUTES (Hanya Seller)
// =========================================================================
Route::middleware(['auth:sanctum', 'verified.email', 'active.role:seller'])->group(function () {
    
    // Manajemen Master Atribut
    Route::prefix('products/attributes')->name('product-attributes.')->group(function () {
        Route::get('/', [ProductAttributeController::class, 'index'])->name('index');
        Route::post('/', [ProductAttributeController::class, 'store'])->name('store');
        Route::get('/{id}', [ProductAttributeController::class, 'show'])->whereNumber('id')->name('show');
        Route::put('/{id}', [ProductAttributeController::class, 'update'])->whereNumber('id')->name('update');
        Route::delete('/{id}', [ProductAttributeController::class, 'destroy'])->whereNumber('id')->name('destroy');
    });

    // Manajemen Internal Produk & Varian (Prefix: seller/products)
    Route::prefix('seller/products')->name('seller.products.')->group(function () {
        Route::get('/', [ProductController::class, 'sellerIndex'])->name('index');
        Route::post('/', [ProductController::class, 'store'])->name('store');
        Route::put('/{id}', [ProductController::class, 'update'])->whereNumber('id')->name('update');
        Route::delete('/{id}', [ProductController::class, 'destroy'])->whereNumber('id')->name('destroy');

        // CRUD Varian oleh Seller
        Route::get('/{productId}/variants', [ProductVariantController::class, 'index'])->whereNumber('productId')->name('variants.index');
        Route::post('/{productId}/variants', [ProductVariantController::class, 'store'])->whereNumber('productId')->name('variants.store');
        Route::get('/{productId}/variants/{variantId}', [ProductVariantController::class, 'show'])->whereNumber('productId')->whereNumber('variantId')->name('variants.show');
        Route::put('/{productId}/variants/{variantId}', [ProductVariantController::class, 'update'])->whereNumber('productId')->whereNumber('variantId')->name('variants.update');
        Route::delete('/{productId}/variants/{variantId}', [ProductVariantController::class, 'destroy'])->whereNumber('productId')->whereNumber('variantId')->name('variants.destroy');
    });
});