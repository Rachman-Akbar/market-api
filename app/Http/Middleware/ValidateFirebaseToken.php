<?php

namespace App\Http\Middleware;

use App\Domains\Identity\Infrastructure\Firebase\FirebaseTokenVerifier;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;
use Throwable;

final class ValidateFirebaseToken
{
    public function handle(Request $request, Closure $next): Response
    {
        $bearerToken = $request->bearerToken();

        if (! is_string($bearerToken) || trim($bearerToken) === '') {
            return response()->json([
                'message' => 'Firebase ID token is required in Bearer authorization header.',
            ], 401);
        }

        try {
            /** @var FirebaseTokenVerifier $verifier */
            $verifier = app(FirebaseTokenVerifier::class);

            $claims = $verifier->verify($bearerToken);

            $request->attributes->set('firebase_claims', $claims);

            return $next($request);
        } catch (Throwable $e) {
            Log::error('Firebase token verification failed', [
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
            ]);

            return response()->json([
                'message' => 'Firebase ID token is invalid or could not be verified.',
                'error' => config('app.debug') ? $e->getMessage() : null,
            ], 401);
        }
    }
}