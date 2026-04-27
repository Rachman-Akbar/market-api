<?php

namespace App\Domains\Identity\Infrastructure\Firebase;

use App\Models\User;
use Closure;
use Illuminate\Contracts\Container\Container;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\Response;

final class VerifyFirebaseToken
{
    private FirebaseTokenVerifier $verifier;

    public function __construct(Container $container)
    {
        $this->verifier = $container->make(FirebaseTokenVerifier::class);
    }

    public function handle(Request $request, Closure $next): mixed
    {
        $authHeader = $request->header('Authorization', '');

        if (! preg_match('/^Bearer\s+(.+)$/i', $authHeader, $matches)) {
            return response()->json([
                'message' => 'Missing or invalid Authorization Bearer token.',
            ], Response::HTTP_UNAUTHORIZED);
        }

        $idToken = $matches[1] ?? null;

        if (! $idToken) {
            return response()->json([
                'message' => 'Missing Firebase ID token.',
            ], Response::HTTP_UNAUTHORIZED);
        }

        try {
            $claims = $this->verifier->verify($idToken);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'message' => 'Invalid Firebase ID token.',
            ], Response::HTTP_UNAUTHORIZED);
        } catch (\Throwable $e) {
            return response()->json([
                'message' => 'Could not verify Firebase ID token.',
            ], Response::HTTP_UNAUTHORIZED);
        }

        $firebaseUid = $this->getClaim($claims, 'sub')
            ?? $this->getClaim($claims, 'user_id')
            ?? $this->getClaim($claims, 'uid');

        $email = $this->getClaim($claims, 'email');
        $name = $this->getClaim($claims, 'name');

        if (! $firebaseUid) {
            return response()->json([
                'message' => 'Firebase UID not found in token.',
            ], Response::HTTP_UNAUTHORIZED);
        }

        if (! $email) {
            return response()->json([
                'message' => 'Firebase email not found in token.',
            ], Response::HTTP_UNAUTHORIZED);
        }

        $user = User::query()
            ->where('firebase_uid', $firebaseUid)
            ->orWhere('email', $email)
            ->first();

        if (! $user) {
            $user = new User();

            if (! $user->getKey()) {
                $user->id = (string) Str::uuid();
            }

            if (Schema::hasColumn('users', 'password')) {
                $user->password = Hash::make(Str::random(40));
            }
        }

        $payload = [
            'firebase_uid' => $firebaseUid,
            'email' => $email,
            'name' => $name ?: Str::before($email, '@'),
        ];

        if (Schema::hasColumn('users', 'email_verified_at')) {
            $payload['email_verified_at'] = $this->getClaim($claims, 'email_verified')
                ? now()
                : $user->email_verified_at;
        }

        $user->forceFill($payload);
        $user->save();

        Auth::guard()->setUser($user);

        $request->setUserResolver(static fn () => $user);

        $request->attributes->set('firebase_claims', $claims);
        $request->attributes->set('firebase_uid', $firebaseUid);

        return $next($request);
    }

    private function getClaim(mixed $claims, string $key): mixed
    {
        if (is_array($claims)) {
            return $claims[$key] ?? null;
        }

        if (is_object($claims)) {
            if (method_exists($claims, 'get')) {
                return $claims->get($key);
            }

            if (isset($claims->{$key})) {
                return $claims->{$key};
            }
        }

        return null;
    }
}
