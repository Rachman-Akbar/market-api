<?php

declare(strict_types=1);

namespace App\Domains\Identity\Auth\Presentation\Http\Controllers;

use App\Domains\Identity\Auth\Application\Services\EmailVerificationEngine;
use App\Domains\Identity\Auth\Application\UseCases\BuildAuthPayloadUseCase;
use App\Domains\Identity\Auth\Application\UseCases\ChangePasswordUseCase;
use App\Domains\Identity\Auth\Application\UseCases\LoginUserUseCase;
use App\Domains\Identity\Auth\Application\UseCases\LoginWithFirebaseUseCase;
use App\Domains\Identity\Auth\Application\UseCases\LogoutUserUseCase;
use App\Domains\Identity\Auth\Application\UseCases\RegisterUserUseCase;
use App\Domains\Identity\Auth\Application\UseCases\ResetPasswordUseCase;
use App\Domains\Identity\Auth\Application\UseCases\ResetPasswordWithCodeUseCase;
use App\Domains\Identity\Auth\Application\UseCases\SwitchRoleUseCase;
use App\Domains\Identity\Auth\Infrastructure\Mail\EmailVerificationMail;
use App\Domains\Identity\Auth\Infrastructure\Mail\PasswordChangedMail;
use App\Domains\Identity\Auth\Infrastructure\Mail\PasswordResetMail;
use App\Domains\Identity\Auth\Presentation\Http\Requests\ChangePasswordRequest;
use App\Domains\Identity\Auth\Presentation\Http\Requests\ResetPasswordRequest;
use App\Domains\Identity\User\Application\UseCases\DeleteUserUseCase;
use App\Domains\Identity\User\Domain\Exceptions\EmailAlreadyExistsException;
use App\Http\Controllers\Controller;
use Illuminate\Auth\Passwords\TokenRepositoryInterface;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules\Password as PasswordRule;
use Illuminate\Validation\ValidationException;

final class AuthController extends Controller
{
    public function passwordRegister(Request $request, RegisterUserUseCase $useCase): JsonResponse
    {
        $request->merge([
            'email' => Str::lower(trim((string) $request->input('email'))),
        ]);
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'confirmed', PasswordRule::min(8)],
            'device_name' => ['nullable', 'string', 'max:100'],
        ]);

        try {
            return response()->json($useCase->execute(
                name: $validated['name'],
                email: $validated['email'],
                password: $validated['password'],
                deviceName: $validated['device_name'] ?? 'marketplace-web'
            ), 201);
        } catch (EmailAlreadyExistsException $exception) {
            throw ValidationException::withMessages([
                'email' => [$exception->getMessage()],
            ]);
        }
    }

    public function passwordLogin(Request $request, LoginUserUseCase $useCase): JsonResponse
    {
        $request->merge([
            'email' => Str::lower(trim((string) $request->input('email'))),
        ]);
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

        if (! is_array($firebaseUser)) {
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

        $email = mb_strtolower(trim($validated['email']));

        $userClass = config('auth.providers.users.model');
        $user = $userClass::where('email', $email)->first();

        if ($user) {
            $tokenRepository = app(TokenRepositoryInterface::class);
            $rawToken = $tokenRepository->create($user);

            $resetUrl = env('FRONTEND_URL', config('app.url'))
                .'/auth/reset-password?token='.$rawToken.'&email='.urlencode($email);

            try {
                Mail::to($user->email)->queue(new PasswordResetMail($resetUrl));
            } catch (\Throwable) {
                // Email sending failure should not block the response
            }
        }

        // Always return the same message to prevent email enumeration
        return response()->json([
            'message' => 'Jika email terdaftar, link reset password akan dikirim dalam beberapa menit.',
        ]);
    }

    public function resetPassword(ResetPasswordRequest $request, ResetPasswordUseCase $useCase): JsonResponse
    {
        $validated = $request->validated();

        $useCase->execute(
            email: $validated['email'],
            token: $validated['token'],
            password: $validated['password'],
        );

        return response()->json([
            'message' => 'Password berhasil diubah. Silakan masuk dengan password baru.',
        ]);
    }

    public function sendVerificationCode(Request $request, EmailVerificationEngine $engine): JsonResponse
    {
        $email = (string) $request->user()->email;
        $code = $engine->issue($email);

        try {
            Mail::to($email)->queue(new EmailVerificationMail($code, $engine->ttlMinutes()));
        } catch (\Throwable) {
            // Email sending failure should not block the response
        }

        return response()->json([
            'message' => 'Kode verifikasi telah dikirim ke email Anda.',
            'expires_in_minutes' => $engine->ttlMinutes(),
        ]);
    }

    public function sendPasswordResetCode(Request $request, EmailVerificationEngine $engine): JsonResponse
    {
        $validated = $request->validate([
            'email' => ['required', 'email'],
        ]);

        $email = mb_strtolower(trim($validated['email']));

        $userClass = config('auth.providers.users.model');
        $user = $userClass::where('email', $email)->first();

        if ($user) {
            $code = $engine->issue($email);

            try {
                Mail::to($email)->queue(new EmailVerificationMail($code, $engine->ttlMinutes()));
            } catch (\Throwable) {
                // Email sending failure should not block the response
            }
        }

        // Always return the same message to prevent email enumeration
        return response()->json([
            'message' => 'Jika email terdaftar, kode verifikasi 6 digit akan dikirim dalam beberapa menit.',
        ]);
    }

    public function verifyEmailCode(Request $request, EmailVerificationEngine $engine): JsonResponse
    {
        $validated = $request->validate([
            'email' => ['required', 'email'],
            'code' => ['required', 'string', 'digits:6'],
        ]);

        $email = mb_strtolower(trim($validated['email']));

        $engine->verify($email, $validated['code']);

        $userClass = config('auth.providers.users.model');
        $user = $userClass::where('email', $email)->first();

        if ($user && ! (bool) $user->is_email_verified) {
            $user->forceFill(['is_email_verified' => true])->save();
        }

        return response()->json([
            'message' => 'Email berhasil diverifikasi.',
        ]);
    }

    public function resetPasswordWithCode(Request $request, ResetPasswordWithCodeUseCase $useCase): JsonResponse
    {
        $validated = $request->validate([
            'email' => ['required', 'email'],
            'code' => ['required', 'string', 'digits:6'],
            'password' => ['required', 'string', 'confirmed', PasswordRule::min(8)->mixedCase()->numbers()],
        ]);

        $useCase->execute(
            email: mb_strtolower(trim($validated['email'])),
            code: $validated['code'],
            password: $validated['password'],
        );

        return response()->json([
            'message' => 'Password berhasil diubah. Silakan masuk dengan password baru.',
        ]);
    }

    public function changePassword(ChangePasswordRequest $request, ChangePasswordUseCase $useCase): JsonResponse
    {
        $validated = $request->validated();

        $useCase->execute(
            user: $request->user(),
            currentPassword: $validated['current_password'],
            newPassword: $validated['new_password'],
            verificationCode: $validated['verification_code'],
        );

        try {
            Mail::to($request->user()->email)->queue(new PasswordChangedMail($request->user()->name));
        } catch (\Throwable) {
            // Email sending failure should not block the response
        }

        return response()->json([
            'message' => 'Password berhasil diubah. Semua sesi login lain telah logout.',
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
