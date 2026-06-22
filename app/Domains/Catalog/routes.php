<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;

// Berikan prefix 'catalog' di sini agar menjadi api/v1/catalog/...
Route::prefix('catalog')->group(function () {

    Route::group([], app_path('Domains/Catalog/CatalogGroup/Presentation/routes.php'));
    Route::group([], app_path('Domains/Catalog/Category/Presentation/routes.php'));
    Route::group([], app_path('Domains/Catalog/Product/Presentation/routes.php'));
    Route::group([], app_path('Domains/Catalog/Banner/Presentation/routes.php'));
    Route::group([], app_path('Domains/Catalog/Promotion/Presentation/routes.php'));

});
