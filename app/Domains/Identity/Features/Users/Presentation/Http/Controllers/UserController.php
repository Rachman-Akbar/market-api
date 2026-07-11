<?php

declare(strict_types=1);

namespace App\Domains\Identity\Features\Users\Presentation\Http\Controllers;

use App\Domains\Identity\Domain\Exceptions\EmailAlreadyExistsException;
use App\Domains\Identity\Domain\Exceptions\UserNotFoundException;
use App\Domains\Identity\Domain\Repositories\UserRepositoryInterface;
use App\Domains\Identity\Features\Users\Application\DTOs\CreateUserDTO;
use App\Domains\Identity\Features\Users\Application\DTOs\UpdateUserDTO;
use App\Domains\Identity\Features\Users\Application\Queries\GetUserByEmailQuery;
use App\Domains\Identity\Features\Users\Application\Queries\GetUserQuery;
use App\Domains\Identity\Features\Users\Application\Queries\ListUsersQuery;
use App\Domains\Identity\Features\Users\Application\UseCases\CreateUserUseCase;
use App\Domains\Identity\Features\Users\Application\UseCases\DeleteUserUseCase;
use App\Domains\Identity\Features\Users\Application\UseCases\UpdateUserUseCase;
use App\Domains\Identity\Features\Users\Presentation\Http\Requests\StoreUserRequest;
use App\Domains\Identity\Features\Users\Presentation\Http\Requests\UpdateUserRequest;
use App\Domains\Identity\Features\Users\Presentation\Http\Resources\UserCollection;
use App\Domains\Identity\Features\Users\Presentation\Http\Resources\UserResource;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

final class UserController extends Controller
{
    public function __construct(
        private readonly ListUsersQuery $listUsersUseCase,
        private readonly GetUserQuery $getUserUseCase,
        private readonly GetUserByEmailQuery $getUserByEmailUseCase,
        private readonly CreateUserUseCase $createUserUseCase,
        private readonly UpdateUserUseCase $updateUserUseCase,
        private readonly DeleteUserUseCase $deleteUserUseCase,
        private readonly UserRepositoryInterface $userRepository
    ) {}

    public function index(Request $request): UserCollection
    {
        $perPage = min(100, max(1, (int) $request->query('per_page', 15)));
        return new UserCollection($this->listUsersUseCase->execute($perPage));
    }

    public function show(Request $request, string $id): JsonResponse
    {
        $this->ensureAccess($request, $id);

        try {
            return (new UserResource($this->getUserUseCase->execute($id)))
                ->response()
                ->setStatusCode(Response::HTTP_OK);
        } catch (UserNotFoundException $exception) {
            return response()->json(['message' => $exception->getMessage()], Response::HTTP_NOT_FOUND);
        }
    }

    public function showByEmail(Request $request, string $email): JsonResponse
    {
        try {
            $user = $this->getUserByEmailUseCase->execute($email);
            $this->ensureAccess($request, (string) $user->id);

            return (new UserResource($user))
                ->response()
                ->setStatusCode(Response::HTTP_OK);
        } catch (UserNotFoundException $exception) {
            return response()->json(['message' => $exception->getMessage()], Response::HTTP_NOT_FOUND);
        }
    }

    public function store(StoreUserRequest $request): JsonResponse
    {
        $user = $this->createUserUseCase->execute(CreateUserDTO::fromArray($request->validated()));
        return (new UserResource($user))->response()->setStatusCode(Response::HTTP_CREATED);
    }

    public function update(UpdateUserRequest $request, string $id): JsonResponse
    {
        $activeRole = $this->ensureAccess($request, $id);
        $validated = $request->validated();

        if ($activeRole !== 'admin') {
            unset($validated['firebase_uid'], $validated['is_email_verified'], $validated['role_ids']);
        }

        try {
            $user = $this->updateUserUseCase->execute($id, UpdateUserDTO::fromArray($validated));
            return (new UserResource($user))->response()->setStatusCode(Response::HTTP_OK);
        } catch (UserNotFoundException $exception) {
            return response()->json(['message' => $exception->getMessage()], Response::HTTP_NOT_FOUND);
        } catch (EmailAlreadyExistsException $exception) {
            return response()->json(['message' => $exception->getMessage()], Response::HTTP_UNPROCESSABLE_ENTITY);
        }
    }

    public function destroy(string $id): JsonResponse
    {
        try {
            $this->deleteUserUseCase->execute($id);
            return response()->json([
                'success' => true,
                'message' => 'User deleted successfully.',
            ]);
        } catch (UserNotFoundException $exception) {
            return response()->json(['message' => $exception->getMessage()], Response::HTTP_NOT_FOUND);
        }
    }

    private function ensureAccess(Request $request, string $targetUserId): string
    {
        $authenticatedUser = $request->user();
        $activeRole = $this->userRepository->getActiveRoleFromCurrentToken($authenticatedUser);

        if ($activeRole === 'admin' || (string) $authenticatedUser->id === $targetUserId) {
            return (string) ($activeRole ?: 'buyer');
        }

        throw new AccessDeniedHttpException('Anda tidak dapat mengakses data pengguna lain.');
    }
}
