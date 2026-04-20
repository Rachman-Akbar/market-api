<?php

use Illuminate\Support\Facades\Route;
use App\Domains\Catalog\Presentation\Http\Controllers\ProductController;
use App\Domains\Catalog\Presentation\Http\Controllers\EntityController;
use App\Domains\Catalog\Presentation\Http\Controllers\CategoryController;

Route::prefix('catalog')->group(function () {

    Route::get('products', [ProductController::class, 'index']);
    Route::get('products/{id}', [ProductController::class, 'show']);

    Route::get('entities', [EntityController::class, 'index']);
    Route::post('entities', [EntityController::class, 'store']);
    Route::put('entities/{id}', [EntityController::class, 'update']);

    Route::get('categories', [CategoryController::class, 'index']);
    Route::post('categories', [CategoryController::class, 'store']);

});
