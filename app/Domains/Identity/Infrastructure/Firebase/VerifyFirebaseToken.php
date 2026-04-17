<?php

namespace App\Domains\Identity\Infrastructure\Firebase;

use Closure;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Contracts\Container\Container;

final class VerifyFirebaseToken
{
    private FirebaseTokenVerifier $verifier;

    public function __construct(Container $container)
    {
        // Resolve via container for dependency injection
        $this->verifier = $container->make(FirebaseTokenVerifier::class);
    }

    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next): mixed
    {
        $authHeader = $request->header('Authorization', '');
        if (!preg_match('/^Bearer\\s+(.+)$/i', $authHeader, $matches)) {
            return response()->json(['message' => 'Missing or invalid Authorization Bearer token.'], Response::HTTP_UNAUTHORIZED);
        }
        $idToken = $matches[1] ?? null;
        if (!$idToken) {
            return response()->json(['message' => 'Missing Firebase ID token.'], Response::HTTP_UNAUTHORIZED);
        }

        try {
            $claims = $this->verifier->verify($idToken);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json(['message' => 'Invalid Firebase ID token.'], Response::HTTP_UNAUTHORIZED);
        } catch (\Throwable $e) {
            return response()->json(['message' => 'Could not verify Firebase ID token.'], Response::HTTP_UNAUTHORIZED);
        }

        $request->attributes->set('firebase_claims', $claims);

        return $next($request);
    }
}
