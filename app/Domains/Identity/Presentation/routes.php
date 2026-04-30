<?php

use App\Http\Middleware\EnsureApiTokenIsValid;
use App\Http\Middleware\ValidateFirebaseToken;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

function authUserPayload(User $user): array
{
    $roles = $user->roles()->pluck('name')->values();

    return [
        'user' => [
            'id' => $user->id,
            'firebase_uid' => $user->firebase_uid,
            'email' => $user->email,
            'name' => $user->name,
            'avatar' => $user->avatar,
            'is_email_verified' => $user->is_email_verified,
        ],
        'roles' => $roles,
        'active_role' => $roles->first() ?? 'customer',
    ];
}

Route::prefix('identity/auth')->group(function () {
    Route::middleware([ValidateFirebaseToken::class])->group(function () {
        Route::post('/firebase-login', function (Request $request) {
            /** @var User $user */
            $user = $request->user();

            $token = $user->createToken('web-session', ['web'])->plainTextToken;

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

            $token = $user->createToken('web-session', ['web'])->plainTextToken;

            return response()->json([
                ...authUserPayload($user->fresh()),
                'api_token' => $token,
            ], 201);
        });
    });

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