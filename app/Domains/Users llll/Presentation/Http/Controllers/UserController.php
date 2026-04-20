<?php

namespace App\Domains\Users\Presentation\Http\Controllers;

use App\Domains\Users\Application\Actions\CreateAddressAction;
use App\Domains\Users\Application\Actions\ListAddressesAction;
use App\Domains\Users\Application\Actions\UpsertSellerProfileAction;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

final class UserController extends Controller
{
    public function updateSellerProfile(Request $request, UpsertSellerProfileAction $action): JsonResponse
    {
        $validated = $request->validate([
            'name' => ['sometimes', 'string', 'max:255'],
            'avatar' => ['sometimes', 'nullable', 'string'],
        ]);

        /** @var \App\Models\User $user */
        $user = $request->user();

        return response()->json([
            'data' => $action->execute($user, $validated),
        ]);
    }

    public function createAddress(Request $request, CreateAddressAction $action): JsonResponse
    {
        $validated = $request->validate([
            'label' => ['required', 'string', 'max:100'],
            'address' => ['required', 'string'],
            'lat' => ['nullable', 'numeric'],
            'lng' => ['nullable', 'numeric'],
        ]);

        /** @var \App\Models\User $user */
        $user = $request->user();

        return response()->json([
            'data' => $action->execute($user->id, $validated),
        ], 201);
    }

    public function listAddresses(Request $request, ListAddressesAction $action): JsonResponse
    {
        /** @var \App\Models\User $user */
        $user = $request->user();

        return response()->json([
            'data' => $action->execute($user->id),
        ]);
    }
}
