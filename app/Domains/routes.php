<?php

declare(strict_types=1);

use App\Domains\Communication\CommunicationServiceProvider;
use Illuminate\Support\Facades\Route;

Route::group([], app_path('Domains/Identity/routes.php'));
Route::group([], app_path('Domains/Catalog/routes.php'));
Route::group([], app_path('Domains/Seller/routes.php'));
Route::group([], app_path('Domains/Admin/routes.php'));
Route::group([], app_path('Domains/Order/routes.php'));
Route::group([], app_path('Domains/Support/routes.php'));
Route::group([], app_path('Domains/Engagement/routes.php'));

if (app()->getProviders(CommunicationServiceProvider::class) === []) {
    app()->register(CommunicationServiceProvider::class);
}

$communicationRouteExists = collect(Route::getRoutes()->getRoutes())->contains(
    fn ($route): bool => $route->uri() === 'api/v1/communication/conversations' && in_array('POST', $route->methods(), true)
);

if (! $communicationRouteExists) {
    Route::group([], app_path('Domains/Communication/routes.php'));
}
