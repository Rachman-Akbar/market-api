<?php

namespace App\Domains\Identity\Presentation\Http\Controllers;

use App\Domains\Identity\Application\Actions\LoginWithFirebaseAction;
use App\Domains\Identity\Application\Actions\RegisterWithPasswordAction;
use App\Domains\Identity\Application\Actions\SwitchRoleAction;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

final class AuthController extends Controller
{
    public function me(Request $request): JsonResponse
    {
        /** @var \App\Models\User $user */
        $user = $request->user();

        return response()->json([
            'user' => [
                'id' => $user->id,
                'firebase_uid' => $user->firebase_uid,
                'email' => $user->email,
                'name' => $user->name,
                'avatar' => $user->avatar,
                'is_email_verified' => (bool) ($user->is_email_verified ?? false),
            ],
        ]);
    }

    public function logout(Request $request): JsonResponse
    {
        /** @var \App\Models\User $user */
        $user = $request->user();

        /** @var \Laravel\Sanctum\PersonalAccessToken|null $token */
        $token = $user->currentAccessToken();

        if ($token !== null) {
            $user->tokens()->where('id', $token->id)->delete();
        }

        return response()->json([
            'message' => 'Logged out successfully.',
        ]);
    }

    public function register(Request $request, RegisterWithPasswordAction $action): JsonResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        return response()->json(
            $action->execute($validated['name'], $validated['email'], $validated['password']),
            201
        );
    }


    public function firebaseLogin(Request $request, LoginWithFirebaseAction $action): JsonResponse
    {
        /** @var array<string, mixed> $claims */
        $claims = $request->attributes->get('firebase_claims', []);

        return response()->json($action->execute($claims));
    }

    public function switchRole(Request $request, SwitchRoleAction $action): JsonResponse
    {
        $validated = $request->validate([
            'role' => ['required', 'string'],
        ]);

        /** @var \App\Models\User $user */
        $user = $request->user();

        return response()->json($action->execute($user, $validated['role']));
    }

    public function firebaseLoginInfo(): JsonResponse
    {
        return response()->json([
            'message' => 'Use POST and pass Firebase ID token in Authorization Bearer header.',
        ]);
    }
}
