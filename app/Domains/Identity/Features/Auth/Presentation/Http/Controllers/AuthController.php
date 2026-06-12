<?php

declare(strict_types=1);

namespace App\Domains\Identity\Features\Auth\Presentation\Http\Controllers;

use App\Domains\Identity\Features\Auth\Application\UseCases\RegisterUserUseCase;
use App\Domains\Identity\Features\Auth\Application\UseCases\LoginUserUseCase;
use App\Domains\Identity\Features\Auth\Application\UseCases\LoginWithFirebaseUseCase;
use App\Domains\Identity\Features\Auth\Application\UseCases\LogoutUserUseCase;
use App\Domains\Identity\Features\Auth\Application\UseCases\BuildAuthPayloadUseCase;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rules\Password;

final class AuthController extends Controller
{
    /**
     * Register dengan nama, email & password
     */
    public function passwordRegister(Request $request, RegisterUserUseCase $useCase): JsonResponse
    {
        $validated = $request->validate([
            'name'        => ['required', 'string', 'max:255'],
            'email'       => ['required', 'email:rfc,dns', 'max:255', 'unique:users,email'],
            'password'    => ['required', 'confirmed', Password::min(8)],
            'device_name' => ['nullable', 'string', 'max:100'],
        ]);

        $result = $useCase->execute(
            name: $validated['name'],
            email: $validated['email'],
            password: $validated['password'],
            deviceName: $validated['device_name'] ?? 'marketplace-web'
        );

        return response()->json($result, 201);
    }

    /**
     * Login dengan email & password
     */
    public function passwordLogin(Request $request, LoginUserUseCase $useCase): JsonResponse
    {
        $validated = $request->validate([
            'email'       => ['required', 'email'],
            'password'    => ['required', 'string'],
            'device_name' => ['nullable', 'string', 'max:100'],
        ]);

        $result = $useCase->execute(
            email: $validated['email'],
            password: $validated['password'],
            deviceName: $validated['device_name'] ?? 'marketplace-web'
        );

        return response()->json($result);
    }

    /**
     * Login / Register otomatis menggunakan SDK Google Firebase
     */
    public function firebaseLogin(Request $request, LoginWithFirebaseUseCase $useCase): JsonResponse
    {
        $validated = $request->validate([
            'device_name' => ['nullable', 'string', 'max:100'],
        ]);

        $firebaseUser = $request->attributes->get('firebase_user');

        if (!is_array($firebaseUser)) {
            return response()->json([
                'message' => 'Firebase user payload is missing.',
            ], 401);
        }

        $result = $useCase->execute(
            firebaseUser: $firebaseUser,
            deviceName: $validated['device_name'] ?? 'marketplace-web'
        );

        return response()->json($result);
    }

    /**
     * Ambil data sesi profil user saat ini
     */
    public function me(Request $request, BuildAuthPayloadUseCase $payload): JsonResponse
    {
        return response()->json($payload->execute($request->user()));
    }

    /**
     * Logout dari device yang saat ini digunakan
     */
    public function logoutCurrentDevice(Request $request, LogoutUserUseCase $useCase): JsonResponse
    {
        $useCase->execute($request->user(), 'current');

        return response()->json(['message' => 'Logged out from current device.']);
    }

    /**
     * Logout dari semua device lain kecuali yang sedang aktif
     */
    public function logoutOtherDevices(Request $request, LogoutUserUseCase $useCase): JsonResponse
    {
        $deletedCount = $useCase->execute($request->user(), 'other');

        return response()->json([
            'message'        => 'Other devices logged out.',
            'deleted_tokens' => $deletedCount,
        ]);
    }

    /**
     * Logout dari seluruh sesi di semua device
     */
    public function logoutAllDevices(Request $request, LogoutUserUseCase $useCase): JsonResponse
    {
        $deletedCount = $useCase->execute($request->user(), 'all');

        return response()->json([
            'message'        => 'Logged out from all devices.',
            'deleted_tokens' => $deletedCount,
        ]);
    }
}




