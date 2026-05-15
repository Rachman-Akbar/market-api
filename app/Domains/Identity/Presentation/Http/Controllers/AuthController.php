<?php

namespace App\Domains\Identity\Presentation\Http\Controllers;

use App\Domains\Identity\Application\Actions\BuildAuthPayloadAction;
use App\Domains\Identity\Application\Actions\LoginWithFirebaseAction;
use App\Domains\Identity\Application\Actions\RegisterWithPasswordAction;
use App\Domains\Identity\Application\Actions\SwitchRoleAction;
use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Laravel\Sanctum\PersonalAccessToken;

final class AuthController extends Controller
{
    public function me(
        Request $request,
        BuildAuthPayloadAction $payload
    ): JsonResponse {
        /** @var User $user */
        $user = $request->user();

        return response()->json(
            $payload->execute($user)
        );
    }

    public function register(
        Request $request,
        RegisterWithPasswordAction $action
    ): JsonResponse {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => [
                'required',
                'string',
                'email',
                'max:255',
                Rule::unique('users', 'email'),
            ],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        return response()->json(
            $action->execute(
                name: $validated['name'],
                email: $validated['email'],
                password: $validated['password'],
            ),
            201
        );
    }

    public function firebaseLogin(
        Request $request,
        LoginWithFirebaseAction $action
    ): JsonResponse {
        /**
         * User sudah dibuat / disinkronkan oleh ValidateFirebaseToken.
         */
        /** @var User $user */
        $user = $request->user();

        return response()->json(
            $action->execute($user)
        );
    }

    public function firebaseRegister(
        Request $request,
        LoginWithFirebaseAction $action
    ): JsonResponse {
        /**
         * User juga sudah dibuat / disinkronkan oleh ValidateFirebaseToken.
         * Di sini hanya optional update nama dari form register frontend.
         */
        /** @var User $user */
        $user = $request->user();

        if ($request->filled('name')) {
            $user->forceFill([
                'name' => $request->string('name')->toString(),
            ])->save();
        }

        return response()->json(
            $action->execute($user->fresh()),
            201
        );
    }

    public function switchRole(
    Request $request,
    SwitchRoleAction $action
): JsonResponse {
    $validated = $request->validate([
        'role' => ['bail', 'required', 'string', 'in:buyer,seller,admin'],
    ]);

    /** @var User $user */
    $user = $request->user();

    return response()->json(
        $action->execute($user, $validated['role'])
    );
}

    public function logout(Request $request): JsonResponse
{
    /** @var User|null $user */
    $user = $request->user();

    $token = $user?->currentAccessToken();

    if ($token instanceof PersonalAccessToken) {
        $token->delete();
    }

    return response()->json([
        'message' => 'Logged out successfully.',
    ]);
}
    public function firebaseLoginInfo(): JsonResponse
    {
        return response()->json([
            'message' => 'Use POST and pass Firebase ID token in Authorization Bearer header.',
        ]);
    }
}
