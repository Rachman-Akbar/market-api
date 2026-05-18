<?php

declare(strict_types=1);

namespace App\Domains\Identity\Presentation\Http\Controllers;

use App\Domains\Identity\Application\Actions\BuildAuthPayloadAction;
use App\Domains\Identity\Application\Actions\LoginWithFirebaseAction;
use App\Domains\Identity\Application\Actions\SwitchRoleAction;
use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

final class AuthController extends Controller
{
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

    public function switchRole(
        Request $request,
        SwitchRoleAction $switchRole,
        BuildAuthPayloadAction $payload,
    ): JsonResponse {
        $validated = $request->validate([
            'role' => ['required', 'string', Rule::in(['buyer', 'seller', 'admin'])],
        ]);

        /** @var User|null $user */
        $user = $request->user();

        if (! $user instanceof User) {
            return response()->json([
                'message' => 'Unauthenticated.',
            ], 401);
        }

        $activeRole = $switchRole->execute(
            user: $user,
            role: $validated['role'],
        );

        return response()->json(
            $payload->execute(
                user: $user,
                activeRole: $activeRole,
            ),
        );
    }

    public function logout(Request $request): JsonResponse
    {
        /** @var User|null $user */
        $user = $request->user();

        $user?->currentAccessToken()?->delete();

        return response()->json([
            'message' => 'Logged out.',
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