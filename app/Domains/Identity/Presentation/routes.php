<?php

use App\Http\Middleware\EnsureApiTokenIsValid;
use App\Http\Middleware\ValidateFirebaseToken;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Laravel\Sanctum\PersonalAccessToken;

function authUserPayload(User $user): array
{
    $roles = $user->roles()->pluck('name')->unique()->values();

    return [
        'user' => [
            'id' => $user->id,
            'firebase_uid' => $user->firebase_uid,
            'email' => $user->email,
            'name' => $user->name,
            'avatar' => $user->avatar,
            'is_email_verified' => (bool) $user->is_email_verified,
        ],
        'roles' => $roles,
        'active_role' => $roles->first() ?? 'buyer',
    ];
}

Route::prefix('identity/auth')->group(function () {
    /**
     * DEBUG ROUTE
     *
     * Harus di luar ValidateFirebaseToken dan di luar auth:sanctum.
     * Route ini hanya untuk mengecek apakah Authorization Bearer sampai ke Laravel
     * dan apakah Sanctum bisa menemukan tokennya.
     */
    Route::get('/debug-bearer', function (Request $request) {
        $plainToken = $request->bearerToken();

        $accessToken = $plainToken
            ? PersonalAccessToken::findToken($plainToken)
            : null;

        $tokenable = $accessToken?->tokenable;

        return response()->json([
            'authorization_header' => $request->header('Authorization'),
            'bearer_token_exists' => $plainToken !== null,
            'bearer_token_start' => $plainToken ? substr($plainToken, 0, 15) : null,

            'token_found_by_sanctum' => $accessToken !== null,
            'token_id' => $accessToken?->id,
            'tokenable_type' => $accessToken?->tokenable_type,
            'tokenable_id' => $accessToken?->tokenable_id,

            'tokenable_loaded' => $tokenable !== null,
            'tokenable_class' => $tokenable ? get_class($tokenable) : null,
            'tokenable_user_id' => $tokenable?->id,
        ]);
    });

    /**
     * Firebase login/register.
     * Route ini pakai Firebase ID token.
     */
    Route::middleware([ValidateFirebaseToken::class])->group(function () {
        Route::post('/firebase-login', function (Request $request) {
            /** @var User $user */
            $user = $request->user();

            $roles = $user->roles()->pluck('name')->unique()->values();

            $abilities = $roles
                ->map(fn (string $role) => "role:{$role}")
                ->push('web')
                ->unique()
                ->values()
                ->all();

            $token = $user->createToken('web-session', $abilities)->plainTextToken;

            return response()->json([
                ...authUserPayload($user),
                'api_token' => $token,
            ]);
        });

        Route::post('/firebase-register', function (Request $request) {
            /** @var User $user */
            $user = $request->user();

            if ($request->filled('name')) {
                $user->forceFill([
                    'name' => $request->string('name')->toString(),
                ])->save();
            }

            $roles = $user->roles()->pluck('name')->unique()->values();

            $abilities = $roles
                ->map(fn (string $role) => "role:{$role}")
                ->push('web')
                ->unique()
                ->values()
                ->all();

            $token = $user->createToken('web-session', $abilities)->plainTextToken;

            return response()->json([
                ...authUserPayload($user->fresh()),
                'api_token' => $token,
            ], 201);
        });
    });

    /**
     * Sanctum protected routes.
     * Route ini pakai api_token Sanctum, bukan Firebase ID token.
     */
    Route::middleware(['auth:sanctum', EnsureApiTokenIsValid::class])->group(function () {
        Route::get('/me', function (Request $request) {
            /** @var User $user */
            $user = $request->user();

            return response()->json(authUserPayload($user));
        });

        Route::post('/logout', function (Request $request) {
            $request->user()?->currentAccessToken()?->delete();

            return response()->json([
                'message' => 'Logged out successfully.',
            ]);
        });
    });
});