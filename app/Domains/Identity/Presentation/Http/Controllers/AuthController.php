<?php

declare(strict_types=1);

namespace App\Domains\Identity\Presentation\Http\Controllers;

use App\Domains\Identity\Application\Actions\BuildAuthPayloadAction;
use App\Domains\Identity\Application\Actions\LoginWithFirebaseAction;
// use App\Domains\Identity\Application\Actions\SwitchRoleAction;
use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;
use Illuminate\Validation\ValidationException;
use Laravel\Sanctum\PersonalAccessToken;

final class AuthController extends Controller
{
    public function passwordRegister(
    Request $request,
    BuildAuthPayloadAction $payload,
): JsonResponse {
    $validated = $request->validate([
        'name' => ['required', 'string', 'max:255'],
        'email' => ['required', 'email:rfc,dns', 'max:255', 'unique:users,email'],
        'password' => ['required', 'confirmed', Password::min(8)],
        'device_name' => ['nullable', 'string', 'max:100'],
    ]);

    $user = DB::transaction(function () use ($validated): User {
        /** @var User $user */
        $user = User::query()->create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'firebase_uid' => null,
            'is_email_verified' => false,
        ]);

        /**
         * Kalau kamu pakai Spatie Permission:
         * $user->assignRole('buyer');
         *
         * Kalau kamu pakai table pivot custom, isi role buyer di sini.
         */

        return $user;
    });

    $deviceName = $validated['device_name'] ?? 'marketplace-web';
    $token = $user->createToken($deviceName)->plainTextToken;

    $authPayload = $payload->execute(user: $user);

    return response()->json([
        ...$authPayload,
        'token_type' => 'Bearer',
        'api_token' => $token,
        'access_token' => $token,
    ], 201);
}

public function passwordLogin(
    Request $request,
    BuildAuthPayloadAction $payload,
): JsonResponse {
    $validated = $request->validate([
        'email' => ['required', 'email'],
        'password' => ['required', 'string'],
        'device_name' => ['nullable', 'string', 'max:100'],
    ]);

    /** @var User|null $user */
    $user = User::query()
        ->where('email', $validated['email'])
        ->first();

    if (! $user instanceof User || ! Hash::check($validated['password'], (string) $user->password)) {
        throw ValidationException::withMessages([
            'email' => ['Email atau password salah.'],
        ]);
    }

    if (method_exists($user, 'trashed') && $user->trashed()) {
        throw ValidationException::withMessages([
            'email' => ['Akun tidak ditemukan.'],
        ]);
    }

    $deviceName = $validated['device_name'] ?? 'marketplace-web';
    $token = $user->createToken($deviceName)->plainTextToken;

    $authPayload = $payload->execute(user: $user);

    return response()->json([
        ...$authPayload,
        'token_type' => 'Bearer',
        'api_token' => $token,
        'access_token' => $token,
    ]);
}

    public function firebaseLogin(
        Request $request,
        LoginWithFirebaseAction $loginWithFirebase,
    ): JsonResponse {
        $validated = $request->validate([
            'device_name' => ['nullable', 'string', 'max:100'],
        ]);

        $firebaseUser = $request->attributes->get('firebase_user');

        if (! is_array($firebaseUser)) {
            return response()->json([
                'message' => 'Firebase user payload is missing.',
            ], 401);
        }

        $payload = $loginWithFirebase->execute(
            firebaseUser: $firebaseUser,
            deviceName: $validated['device_name'] ?? null,
        );

        return response()->json($payload);
    }

    public function me(
        Request $request,
        BuildAuthPayloadAction $payload,
    ): JsonResponse {
        /** @var User|null $user */
        $user = $request->user();

        if (! $user instanceof User) {
            return response()->json([
                'message' => 'Unauthenticated.',
            ], 401);
        }

        if (method_exists($user, 'trashed') && $user->trashed()) {
            $user->currentAccessToken()?->delete();

            return response()->json([
                'message' => 'Account not found.',
                'code' => 'ACCOUNT_NOT_FOUND',
            ], 404);
        }

        return response()->json(
            $payload->execute(user: $user),
        );
    }

    public function logoutCurrentDevice(Request $request): JsonResponse
{
    /** @var User|null $user */
    $user = $request->user();

    if (! $user instanceof User) {
        return response()->json([
            'message' => 'Unauthenticated.',
        ], 401);
    }

    $token = $user->currentAccessToken();

    if ($token instanceof PersonalAccessToken) {
        $token->delete();
    }

    return response()->json([
        'message' => 'Logged out from current device.',
    ]);
}

public function logoutOtherDevices(Request $request): JsonResponse
{
    /** @var User|null $user */
    $user = $request->user();

    if (! $user instanceof User) {
        return response()->json([
            'message' => 'Unauthenticated.',
        ], 401);
    }

    $token = $user->currentAccessToken();

    if (! $token instanceof PersonalAccessToken) {
        return response()->json([
            'message' => 'Missing or invalid access token.',
        ], 401);
    }

    $deletedCount = $user->tokens()
        ->where('id', '!=', $token->id)
        ->delete();

    return response()->json([
        'message' => 'Other devices logged out.',
        'deleted_tokens' => $deletedCount,
    ]);
}

public function logoutAllDevices(Request $request): JsonResponse
{
    /** @var User|null $user */
    $user = $request->user();

    if (! $user instanceof User) {
        return response()->json([
            'message' => 'Unauthenticated.',
        ], 401);
    }

    $deletedCount = $user->tokens()->delete();

    return response()->json([
        'message' => 'Logged out from all devices.',
        'deleted_tokens' => $deletedCount,
    ]);
}

    public function deleteCurrentAccount(Request $request): JsonResponse
    {
        /** @var User|null $user */
        $user = $request->user();

        if (! $user instanceof User) {
            return response()->json([
                'message' => 'Unauthenticated.',
            ], 401);
        }

        DB::transaction(function () use ($user): void {
            /**
             * Observer juga membersihkan tokens dan cart.
             * Ini tetap aman sebagai double safety.
             */
            $user->tokens()->delete();

            $user->delete();
        });

        return response()->json([
            'message' => 'Account deleted.',
        ]);
    }
}
