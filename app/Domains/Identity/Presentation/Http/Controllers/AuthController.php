<?php

namespace App\Domains\Identity\Presentation\Http\Controllers;

use App\Domains\Identity\Application\Actions\LoginWithFirebaseAction;
use App\Domains\Identity\Application\Actions\SwitchRoleAction;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

final class AuthController extends Controller
{
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
