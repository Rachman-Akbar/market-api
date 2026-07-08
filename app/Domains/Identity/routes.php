<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;

// Berikan prefix 'identity' di sini agar menjadi api/v1/identity/...
Route::prefix('identity')->group(function () {

    // Me-load sub-route feature Auth

    Route::group([], app_path('Domains/Identity/Features/Auth/Presentation/routes.php'));

    // Me-load sub-route feature Users
    Route::group([], app_path('Domains/Identity/Features/Users/Presentation/routes.php'));

});
