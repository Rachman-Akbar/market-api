<?php

declare(strict_types=1);

namespace App\Domains\Identity\Presentation\Http\Controllers;

use App\Domains\Identity\Application\Actions\BuildAuthPayloadAction;
use App\Domains\Identity\Application\Actions\LoginWithFirebaseAction;
use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;
use Illuminate\Validation\ValidationException;
use Laravel\Sanctum\PersonalAccessToken;

final class AuthController extends Controller
{
    /**
     * Register dengan email & password
     */
    public function passwordRegister(
        Request $request,
        BuildAuthPayloadAction $payload,
    ): JsonResponse {
        $validated = $request->validate([
            'name'        => ['required', 'string', 'max:255'],
            'email'       => ['required', 'email:rfc,dns', 'max:255', 'unique:users,email'],
            'password'    => ['required', 'confirmed', Password::min(8)],
            'device_name' => ['nullable', 'string', 'max:100'],
        ]);

        $user = DB::transaction(function () use ($validated): User {
            /** @var User $user */
            $user = User::query()->create([
                'name'              => $validated['name'],
                'email'             => $validated['email'],
                'password'          => Hash::make($validated['password']),
                'firebase_uid'      => null,
                'is_email_verified' => false,
            ]);

            // Assign default role buyer
            app(\App\Domains\Identity\Infrastructure\Persistence\Eloquent\UserRepository::class)
                ->assignRoleByName($user, 'buyer');

            return $user;
        });

        $deviceName = $validated['device_name'] ?? 'marketplace-web';
        $token = $user->createToken($deviceName)->plainTextToken;

        $authPayload = $payload->execute($user);

        return response()->json([
            ...$authPayload,
            'token_type'   => 'Bearer',
            'api_token'    => $token,
            'access_token' => $token,
        ], 201);
    }

    /**
     * Login dengan email & password
     */
    public function passwordLogin(
        Request $request,
        BuildAuthPayloadAction $payload,
    ): JsonResponse {
        $validated = $request->validate([
            'email'       => ['required', 'email'],
            'password'    => ['required', 'string'],
            'device_name' => ['nullable', 'string', 'max:100'],
        ]);

        /** @var User|null $user */
        $user = User::query()
            ->where('email', $validated['email'])
            ->first();

        if (!$user || !Hash::check($validated['password'], (string) $user->password)) {
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

        $authPayload = $payload->execute($user);

        return response()->json([
            ...$authPayload,
            'token_type'   => 'Bearer',
            'api_token'    => $token,
            'access_token' => $token,
        ]);
    }

    /**
     * Login dengan Google (Firebase)
     */
    public function firebaseLogin(
        Request $request,
        LoginWithFirebaseAction $loginWithFirebase,
    ): JsonResponse {
        $validated = $request->validate([
            'device_name' => ['nullable', 'string', 'max:100'],
        ]);

        $firebaseUser = $request->attributes->get('firebase_user');

        if (!is_array($firebaseUser)) {
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

    /**
     * Get current user session
     */
    public function me(
        Request $request,
        BuildAuthPayloadAction $payload,
    ): JsonResponse {
        $user = $request->user();

        if (!$user instanceof User) {
            return response()->json(['message' => 'Unauthenticated.'], 401);
        }

        return response()->json($payload->execute($user));
    }

    /**
     * Logout device saat ini
     */
    public function logoutCurrentDevice(Request $request): JsonResponse
    {
        $user = $request->user();

        if (!$user instanceof User) {
            return response()->json(['message' => 'Unauthenticated.'], 401);
        }

        $token = $user->currentAccessToken();
        if ($token instanceof PersonalAccessToken) {
            $token->delete();
        }

        Cache::forget("auth_payload_{$user->id}");

        return response()->json([
            'message' => 'Logged out from current device.'
        ]);
    }

    /**
     * Logout device lain (kecuali yang sedang digunakan)
     */
    public function logoutOtherDevices(Request $request): JsonResponse
    {
        $user = $request->user();

        if (!$user instanceof User) {
            return response()->json(['message' => 'Unauthenticated.'], 401);
        }

        $currentToken = $user->currentAccessToken();

        $deletedCount = $user->tokens()
            ->when($currentToken, fn($q) => $q->where('id', '!=', $currentToken->id))
            ->delete();

        Cache::forget("auth_payload_{$user->id}");

        return response()->json([
            'message' => 'Other devices logged out.',
            'deleted_tokens' => $deletedCount,
        ]);
    }

    /**
     * Logout semua device
     */
    public function logoutAllDevices(Request $request): JsonResponse
    {
        $user = $request->user();

        if (!$user instanceof User) {
            return response()->json(['message' => 'Unauthenticated.'], 401);
        }

        $deletedCount = $user->tokens()->delete();

        Cache::forget("auth_payload_{$user->id}");

        return response()->json([
            'message' => 'Logged out from all devices.',
            'deleted_tokens' => $deletedCount,
        ]);
    }

    /**
     * Hapus akun saat ini
     */
    public function deleteCurrentAccount(Request $request): JsonResponse
    {
        $user = $request->user();

        if (!$user instanceof User) {
            return response()->json(['message' => 'Unauthenticated.'], 401);
        }

        DB::transaction(function () use ($user): void {
            $user->tokens()->delete();
            $user->delete();
        });

        return response()->json([
            'message' => 'Account deleted successfully.',
        ]);
    }
}