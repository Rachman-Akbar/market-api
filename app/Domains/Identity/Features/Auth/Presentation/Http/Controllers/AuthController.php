<?php

declare(strict_types=1);

namespace App\Domains\Identity\Features\Auth\Presentation\Http\Controllers;

use App\Domains\Identity\Features\Auth\Application\UseCases\BuildAuthPayloadUseCase;
use App\Domains\Identity\Features\Auth\Application\UseCases\LoginUserUseCase;
use App\Domains\Identity\Features\Auth\Application\UseCases\LoginWithFirebaseUseCase;
use App\Domains\Identity\Features\Auth\Application\UseCases\LogoutUserUseCase;
use App\Domains\Identity\Features\Auth\Application\UseCases\RegisterUserUseCase;
use App\Domains\Identity\Features\Auth\Application\UseCases\SwitchRoleUseCase;
use App\Domains\Identity\Features\Users\Application\UseCases\DeleteUserUseCase;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Password;
use Illuminate\Validation\Rules\Password as PasswordRule;
use Illuminate\Validation\ValidationException;

final class AuthController extends Controller
{
    public function passwordRegister(Request $request, RegisterUserUseCase $useCase): JsonResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'confirmed', PasswordRule::min(8)],
            'device_name' => ['nullable', 'string', 'max:100'],
        ]);

        return response()->json($useCase->execute(
            name: $validated['name'],
            email: $validated['email'],
            password: $validated['password'],
            deviceName: $validated['device_name'] ?? 'marketplace-web'
        ), 201);
    }

    public function passwordLogin(Request $request, LoginUserUseCase $useCase): JsonResponse
    {
        $validated = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
            'role' => ['nullable', 'string', 'in:buyer,seller,admin'],
            'device_name' => ['nullable', 'string', 'max:100'],
        ]);

        return response()->json($useCase->execute(
            email: $validated['email'],
            password: $validated['password'],
            deviceName: $validated['device_name'] ?? 'marketplace-web',
            requestedRole: $validated['role'] ?? 'buyer'
        ));
    }

    public function firebaseLogin(Request $request, LoginWithFirebaseUseCase $useCase): JsonResponse
    {
        $validated = $request->validate([
            'role' => ['nullable', 'string', 'in:buyer,seller,admin'],
            'device_name' => ['nullable', 'string', 'max:100'],
        ]);
        $firebaseUser = $request->attributes->get('firebase_user');

        if (!is_array($firebaseUser)) {
            return response()->json(['message' => 'Firebase user payload is missing.'], 401);
        }

        return response()->json($useCase->execute(
            firebaseUser: $firebaseUser,
            deviceName: $validated['device_name'] ?? 'marketplace-web',
            requestedRole: $validated['role'] ?? 'buyer'
        ));
    }

    public function forgotPassword(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'email' => ['required', 'email'],
        ]);

        $status = Password::sendResetLink([
            'email' => strtolower(trim($validated['email'])),
        ]);

        if ($status !== Password::RESET_LINK_SENT) {
            throw ValidationException::withMessages([
                'email' => [__($status)],
            ]);
        }

        return response()->json([
            'message' => __($status),
        ]);
    }

    public function me(Request $request, BuildAuthPayloadUseCase $payload): JsonResponse
    {
        return response()->json($payload->execute($request->user()));
    }

    public function logoutCurrentDevice(Request $request, LogoutUserUseCase $useCase): JsonResponse
    {
        $useCase->execute($request->user(), 'current');

        return response()->json(['message' => 'Logged out from current device.']);
    }

    public function logoutOtherDevices(Request $request, LogoutUserUseCase $useCase): JsonResponse
    {
        $deletedCount = $useCase->execute($request->user(), 'other');

        return response()->json([
            'message' => 'Other devices logged out.',
            'deleted_tokens' => $deletedCount,
        ]);
    }

    public function logoutAllDevices(Request $request, LogoutUserUseCase $useCase): JsonResponse
    {
        $deletedCount = $useCase->execute($request->user(), 'all');

        return response()->json([
            'message' => 'Logged out from all devices.',
            'deleted_tokens' => $deletedCount,
        ]);
    }

    public function switchRole(Request $request, SwitchRoleUseCase $useCase): JsonResponse
    {
        $validated = $request->validate([
            'role' => ['required', 'string', 'in:buyer,seller,admin'],
            'device_name' => ['nullable', 'string', 'max:100'],
        ]);

        return response()->json($useCase->execute(
            user: $request->user(),
            targetRole: $validated['role'],
            deviceName: $validated['device_name'] ?? 'marketplace-web'
        ));
    }

    public function deleteCurrentAccount(Request $request, DeleteUserUseCase $useCase): JsonResponse
    {
        $userId = (string) $request->user()->id;
        $request->user()->tokens()->delete();
        $useCase->execute($userId);

        return response()->json([
            'success' => true,
            'message' => 'Account deleted successfully.',
        ]);
    }
}
