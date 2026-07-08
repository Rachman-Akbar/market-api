<?php

declare(strict_types=1);

namespace App\Domains\Identity;

use App\Domains\Identity\Domain\Repositories\UserRepositoryInterface;
use App\Domains\Identity\Infrastructure\Middleware\FirebaseTokenVerifier;
use App\Domains\Identity\Infrastructure\Persistence\Repositories\EloquentUserRepository;
use Illuminate\Support\ServiceProvider;
use Kreait\Firebase\Factory; // Tambahkan ini
use Kreait\Firebase\Auth;    // Pastikan ini di-import

class IdentityServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        // Bind Interface ke Implementation
        $this->app->bind(UserRepositoryInterface::class, EloquentUserRepository::class);

        // Perbaikan di sini: Gunakan Factory untuk membuat instance Kreait\Firebase\Auth
        $this->app->bind(FirebaseTokenVerifier::class, function ($app) {
    // base_path() akan menggabungkan path dari root project dengan nilai di .env
    $credentialsPath = base_path(env('FIREBASE_CREDENTIALS'));

    $firebaseAuth = (new \Kreait\Firebase\Factory)
        ->withServiceAccount($credentialsPath)
        ->createAuth();

    return new FirebaseTokenVerifier($firebaseAuth);
});

    }

    public function boot(): void
    {
        //
    }
}
