<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;

Route::prefix('seller')->group(function (): void {
    Route::group([], app_path('Domains/Seller/Stores/Presentation/routes.php'));
});
