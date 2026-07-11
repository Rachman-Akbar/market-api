<?php

declare(strict_types=1);

namespace App\Domains\Identity;

use App\Domains\Identity\Domain\Repositories\UserRepositoryInterface;
use App\Domains\Identity\Infrastructure\Middleware\FirebaseTokenVerifier;
use App\Domains\Identity\Infrastructure\Persistence\Repositories\EloquentUserRepository;
use Illuminate\Support\ServiceProvider;
use Kreait\Firebase\Factory;
use RuntimeException;

final class IdentityServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(UserRepositoryInterface::class, EloquentUserRepository::class);

        $this->app->singleton(FirebaseTokenVerifier::class, function (): FirebaseTokenVerifier {
            $configuredPath = trim((string) env('FIREBASE_CREDENTIALS', ''));
            if ($configuredPath === '') {
                throw new RuntimeException('FIREBASE_CREDENTIALS belum dikonfigurasi.');
            }

            $isAbsolute = str_starts_with($configuredPath, DIRECTORY_SEPARATOR)
                || preg_match('/^[A-Za-z]:[\\\\\/]/', $configuredPath) === 1;
            $credentialsPath = $isAbsolute ? $configuredPath : base_path($configuredPath);

            if (!is_file($credentialsPath) || !is_readable($credentialsPath)) {
                throw new RuntimeException('File Firebase credentials tidak ditemukan atau tidak dapat dibaca.');
            }

            $firebaseAuth = (new Factory())
                ->withServiceAccount($credentialsPath)
                ->createAuth();

            return new FirebaseTokenVerifier($firebaseAuth);
        });
    }
}
