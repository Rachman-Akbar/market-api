<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Kreait\Firebase\Contract\Auth;
use App\Domains\Identity\Infrastructure\Middleware\FirebaseTokenVerifier;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        // Firebase bisa tetap di sini jika dianggap sebagai layanan eksternal global
        // Atau lebih baik dipindah ke IdentityServiceProvider jika hanya Auth yang pakai.
        $this->app->bind(FirebaseTokenVerifier::class, function ($app) {
            $auth = $app->make(Auth::class);
            return new FirebaseTokenVerifier($auth);
        });
    }

    public function boot(): void
    {
        // Observer User sebaiknya dipindah ke IdentityServiceProvider di method boot()
    }
}
