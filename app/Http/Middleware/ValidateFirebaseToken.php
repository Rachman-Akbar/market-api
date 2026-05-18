<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Domains\Identity\Infrastructure\Firebase\FirebaseTokenVerifier;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;
use Throwable;

final class ValidateFirebaseToken
{
    public function __construct(
        private readonly FirebaseTokenVerifier $verifier,
    ) {}

    public function handle(Request $request, Closure $next): Response
    {
        $bearerToken = $request->bearerToken();

        if (! is_string($bearerToken) || trim($bearerToken) === '') {
            return response()->json([
                'message' => 'Firebase ID token is required in Bearer authorization header.',
            ], 401);
        }

        try {
            $firebaseUser = $this->verifier->verify($bearerToken);
        } catch (Throwable $e) {
            Log::warning('Firebase token verification failed', [
                'message' => $e->getMessage(),
                'exception' => $e::class,
            ]);

            return response()->json([
                'message' => 'Firebase ID token is invalid.',
            ], 401);
        }

        $request->attributes->set('firebase_user', $firebaseUser);

        return $next($request);
    }
}