<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;

// Berikan prefix 'catalog' di sini agar menjadi api/v1/catalog/...
Route::prefix('order')->group(function () {

    Route::group([], app_path('Domains/Order/Cart/Presentation/routes.php'));
    // Route::group([], app_path('Domains/Order/Category/Presentation/routes.php'));
    // Route::group([], app_path('Domains/Order/Product/Presentation/routes.php'));
    // Route::group([], app_path('Domains/Order/Banner/Presentation/routes.php'));
    // Route::group([], app_path('Domains/Order/Promotion/Presentation/routes.php'));

});
