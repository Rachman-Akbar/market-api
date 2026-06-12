<?php

declare(strict_types=1);

namespace App\Domains\Identity\Features\Users\Presentation\Http\Controllers;

use App\Domains\Identity\Domain\DTOs\CreateUserDTO;
use App\Domains\Identity\Domain\DTOs\UpdateUserDTO;
use App\Domains\Identity\Domain\Exceptions\EmailAlreadyExistsException;
use App\Domains\Identity\Domain\Exceptions\UserNotFoundException;

use App\Domains\Identity\Features\Users\Application\UseCases\CreateUserUseCase;
use App\Domains\Identity\Features\Users\Application\UseCases\DeleteUserUseCase;
use App\Domains\Identity\Features\Users\Application\UseCases\GetUserByEmailUseCase;
use App\Domains\Identity\Features\Users\Application\UseCases\GetUserUseCase;
use App\Domains\Identity\Features\Users\Application\UseCases\ListUsersUseCase;
use App\Domains\Identity\Features\Users\Application\UseCases\UpdateUserUseCase;

use App\Domains\Identity\Features\Users\Presentation\Http\Requests\StoreUserRequest;
use App\Domains\Identity\Features\Users\Presentation\Http\Requests\UpdateUserRequest;
use App\Domains\Identity\Features\Users\Presentation\Http\Resources\UserCollection;
use App\Domains\Identity\Features\Users\Presentation\Http\Resources\UserResource;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class UserController extends Controller
{
    public function __construct(
        private readonly ListUsersUseCase $listUsersUseCase,
        private readonly GetUserUseCase $getUserUseCase,
        private readonly GetUserByEmailUseCase $getUserByEmailUseCase,
        private readonly CreateUserUseCase $createUserUseCase,
        private readonly UpdateUserUseCase $updateUserUseCase,
        private readonly DeleteUserUseCase $deleteUserUseCase
    ) {}

    public function index(Request $request): UserCollection
    {
        $perPage = (int) $request->query('per_page', '15');
        $paginatedUsers = $this->listUsersUseCase->execute($perPage);

        return new UserCollection($paginatedUsers);
    }

    public function show(string $id): JsonResponse
    {
        try {
            $user = $this->getUserUseCase->execute($id);
            return (new UserResource($user))
                ->response()
                ->setStatusCode(Response::HTTP_OK);
        } catch (UserNotFoundException $e) {
            return response()->json(['message' => $e->getMessage()], Response::HTTP_NOT_FOUND);
        }
    }

    public function showByEmail(string $email): JsonResponse
    {
        try {
            $user = $this->getUserByEmailUseCase->execute($email);
            return (new UserResource($user))
                ->response()
                ->setStatusCode(Response::HTTP_OK);
        } catch (UserNotFoundException $e) {
            return response()->json(['message' => $e->getMessage()], Response::HTTP_NOT_FOUND);
        }
    }

 public function store(StoreUserRequest $request, CreateUserUseCase $useCase)
{
    // Ini yang memanggil fungsi yang kita buat di atas
    $dto = CreateUserDTO::fromArray($request->validated());

    $user = $useCase->execute($dto);

    return (new UserResource($user))
        ->response()
        ->setStatusCode(Response::HTTP_CREATED); // Status 201 untuk Create
}

    public function update(UpdateUserRequest $request, string $id): JsonResponse
    {
        try {
            $dto = UpdateUserDTO::fromArray($request->validated());
            $user = $this->updateUserUseCase->execute($id, $dto);

            return (new UserResource($user))
                ->response()
                ->setStatusCode(Response::HTTP_OK);
        } catch (UserNotFoundException $e) {
            return response()->json(['message' => $e->getMessage()], Response::HTTP_NOT_FOUND);
        } catch (EmailAlreadyExistsException $e) {
            return response()->json(['message' => $e->getMessage()], Response::HTTP_UNPROCESSABLE_ENTITY);
        }
    }

   public function destroy(string $id, DeleteUserUseCase $useCase): JsonResponse
{
    try {
        $useCase->execute($id);

        // Jika benar-benar sukses menghapus data yang ADA
        return response()->json(null, Response::HTTP_NO_CONTENT); 
        
    } catch (UserNotFoundException $e) {
        // Jika data tidak ditemukan / sudah terhapus sebelumnya, kembalikan 404!
        return response()->json([
            'message' => $e->getMessage()
        ], Response::HTTP_NOT_FOUND);
    }
}

}
