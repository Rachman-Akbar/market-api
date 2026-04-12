<?php

namespace App\Providers;

use App\Domains\Identity\Infrastructure\Firebase\FirebaseTokenVerifier;
use Kreait\Firebase\Contract\Auth;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind(FirebaseTokenVerifier::class, function ($app): FirebaseTokenVerifier {
            /** @var Auth $auth */
            $auth = $app->make(Auth::class);

            return new FirebaseTokenVerifier($auth);
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
