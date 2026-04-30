<?php

namespace App\Http\Middleware;

use App\Domains\Identity\Infrastructure\Firebase\FirebaseTokenVerifier;
use App\Models\Role;
use App\Models\User;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use LogicException;
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

            $firebaseUser = $verifier->verify($bearerToken);

            $firebaseUid = $firebaseUser['uid'] ?? null;
            $email = $firebaseUser['email'] ?? null;
            $name = $firebaseUser['name'] ?? null;
            $avatar = $firebaseUser['picture'] ?? null;
            $isEmailVerified = (bool) ($firebaseUser['email_verified'] ?? false);

            if (! is_string($firebaseUid) || trim($firebaseUid) === '') {
                return response()->json([
                    'message' => 'Firebase UID is missing from token.',
                ], 401);
            }

            if (! is_string($email) || trim($email) === '') {
                return response()->json([
                    'message' => 'Firebase email is missing from token.',
                ], 401);
            }

            $user = DB::transaction(function () use (
                $firebaseUid,
                $email,
                $name,
                $avatar,
                $isEmailVerified
            ): User {
                $userByUid = User::query()
                    ->where('firebase_uid', $firebaseUid)
                    ->first();

                $userByEmail = User::query()
                    ->where('email', $email)
                    ->first();

                if (
                    $userByUid &&
                    $userByEmail &&
                    $userByUid->id !== $userByEmail->id
                ) {
                    throw new LogicException(
                        'Firebase UID and email are linked to different local users.'
                    );
                }

                if (
                    ! $userByUid &&
                    $userByEmail &&
                    $userByEmail->firebase_uid &&
                    $userByEmail->firebase_uid !== $firebaseUid
                ) {
                    throw new LogicException(
                        'This email is already linked to a different Firebase UID.'
                    );
                }

                $user = $userByUid ?: $userByEmail;

                if (! $user) {
                    $user = User::query()->create([
                        'firebase_uid' => $firebaseUid,
                        'email' => $email,
                        'name' => $name ?: $email,
                        'avatar' => $avatar,
                        'is_email_verified' => $isEmailVerified,
                    ]);
                } else {
                    $user->forceFill([
                        'firebase_uid' => $user->firebase_uid ?: $firebaseUid,
                        'email' => $email,
                        'name' => $name ?: $user->name,
                        'avatar' => $avatar ?: $user->avatar,
                        'is_email_verified' => $isEmailVerified,
                    ])->save();
                }

                $this->assignDefaultRoleIfMissing($user);

                return $user;
            });

            Auth::setUser($user);

            $request->setUserResolver(function () use ($user) {
                return $user;
            });

            $request->attributes->set('firebase_user', $firebaseUser);
            $request->attributes->set('firebase_uid', $firebaseUid);

            return $next($request);
        } catch (Throwable $e) {
            Log::error('Firebase authentication failed', [
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
            ]);

            return response()->json([
                'message' => 'Firebase ID token is invalid or user could not be authenticated.',
                'error' => config('app.debug') ? $e->getMessage() : null,
            ], 401);
        }
    }

    private function assignDefaultRoleIfMissing(User $user): void
    {
        if (! method_exists($user, 'roles')) {
            return;
        }

        if ($user->roles()->exists()) {
            return;
        }

        $role = Role::query()->firstOrCreate(
            ['name' => 'customer'],
            [
                'label' => 'Customer',
                'description' => 'Default customer role.',
            ]
        );

        $user->roles()->syncWithoutDetaching([$role->id]);
    }
}