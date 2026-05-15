<?php

use App\Http\Middleware\EnsureActiveRole;
use App\Http\Middleware\EnsureApiTokenIsValid;
use App\Http\Middleware\EnsureEmailIsVerified;
use App\Http\Middleware\EnsureUserHasRole;
use App\Http\Middleware\ValidateFirebaseToken;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Middleware\HandleCors;
use Illuminate\Http\Request;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__ . '/../routes/web.php',
        api: __DIR__ . '/../routes/api.php',
        commands: __DIR__ . '/../routes/console.php',
        health: '/up',
    )
    ->withProviders([
        App\Domains\Catalog\Infrastructure\Providers\CatalogServiceProvider::class,
        App\Domains\Stores\Infrastructure\Providers\StoreServiceProvider::class,
        App\Domains\Cart\Infrastructure\Providers\CartServiceProvider::class,
        App\Domains\Ordering\Infrastructure\Providers\OrderingServiceProvider::class,

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

            // Tambahkan ini
            'active.role' => EnsureActiveRole::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->render(function (
            AuthenticationException $exception,
            Request $request
        ) {
            if (
                $request->is('api/*') ||
                $request->is('identity/*') ||
                $request->is('catalog/*') ||
                $request->is('seller/*') ||
                $request->expectsJson()
            ) {
                return response()->json([
                    'message' => 'Unauthenticated.',
                ], 401);
            }

            return null;
        });
    })
    ->create();
