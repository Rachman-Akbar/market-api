<?php

namespace App\Http\Middleware;

use App\Domains\Identity\Infrastructure\Firebase\FirebaseTokenVerifier;
use App\Domains\Identity\Infrastructure\Persistence\Eloquent\UserRepository;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use InvalidArgumentException;
use LogicException;
use Symfony\Component\HttpFoundation\Response;
use Throwable;

final class ValidateFirebaseToken
{
    public function __construct(
        private readonly FirebaseTokenVerifier $verifier,
        private readonly UserRepository $users,
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
            Log::error('Firebase token verification failed', [
                'message' => $e->getMessage(),
                'exception' => $e::class,
                'file' => $e->getFile(),
                'line' => $e->getLine(),
            ]);

            return response()->json([
                'message' => 'Firebase ID token is invalid or could not be verified.',
                'error' => config('app.debug') ? $e->getMessage() : null,
                'exception' => config('app.debug') ? $e::class : null,
            ], 401);
        }

        try {
            $user = $this->users->syncFromFirebase($firebaseUser);

            Auth::guard()->setUser($user);

            $request->setUserResolver(static function () use ($user) {
                return $user;
            });

            $request->attributes->set('firebase_user', $firebaseUser);
            $request->attributes->set('firebase_uid', $firebaseUser['uid'] ?? $firebaseUser['sub'] ?? null);

            return $next($request);
        } catch (InvalidArgumentException $e) {
            Log::warning('Firebase token payload is incomplete', [
                'message' => $e->getMessage(),
            ]);

            return response()->json([
                'message' => $e->getMessage(),
            ], 401);
        } catch (LogicException $e) {
            Log::warning('Firebase account conflict', [
                'message' => $e->getMessage(),
            ]);

            return response()->json([
                'message' => 'Firebase account could not be linked to local user.',
                'error' => config('app.debug') ? $e->getMessage() : null,
            ], 409);
        } catch (Throwable $e) {
            Log::error('Firebase user sync failed', [
                'message' => $e->getMessage(),
                'exception' => $e::class,
                'file' => $e->getFile(),
                'line' => $e->getLine(),
            ]);

            return response()->json([
                'message' => 'Firebase user could not be synced to backend.',
                'error' => config('app.debug') ? $e->getMessage() : null,
                'exception' => config('app.debug') ? $e::class : null,
            ], 500);
        }
    }
}