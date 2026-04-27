<?php

use App\Http\Middleware\EnsureApiTokenIsValid;
use App\Http\Middleware\EnsureEmailIsVerified;
use App\Http\Middleware\EnsureUserHasRole;
use App\Http\Middleware\ValidateFirebaseToken;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Middleware\HandleCors;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__ . '/../routes/web.php',
        api: __DIR__ . '/../routes/api.php',
        commands: __DIR__ . '/../routes/console.php',
        health: '/up',
    )
    ->withProviders([
        App\Domains\Catalog\Infrastructure\Providers\CatalogServiceProvider::class,
        App\Domains\Cart\Infrastructure\Providers\CartServiceProvider::class,

        // Aktifkan ini kalau file provider-nya memang ada:
        // App\Domains\Identity\Infrastructure\Providers\IdentityServiceProvider::class,
    ])
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->prepend(HandleCors::class);

        $middleware->alias([
            'api.token' => EnsureApiTokenIsValid::class,
            'firebase.token' => ValidateFirebaseToken::class,
            'firebase.auth' => App\Domains\Identity\Infrastructure\Firebase\VerifyFirebaseToken::class,
            'verified.email' => EnsureEmailIsVerified::class,
            'role' => EnsureUserHasRole::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })
    ->create();
