<?php

namespace App\Http\Middleware;

use App\Domains\Identity\Infrastructure\Firebase\FirebaseTokenVerifier;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpFoundation\Response;

final class ValidateFirebaseToken
{
    public function __construct(private readonly FirebaseTokenVerifier $verifier) {}

    public function handle(Request $request, Closure $next): Response
    {
        $bearerToken = $request->bearerToken();

        if (! is_string($bearerToken) || $bearerToken === '') {
            throw ValidationException::withMessages([
                'authorization' => ['Firebase ID token is required in Bearer authorization header.'],
            ]);
        }

        $claims = $this->verifier->verify($bearerToken);
        $request->attributes->set('firebase_claims', $claims);

        return $next($request);
    }
}
