<?php

namespace App\Domains\Stores\Presentation\Http\Controllers;

use App\Domains\Stores\Application\Actions\BecomeSellerAction;
use App\Domains\Stores\Presentation\Http\Requests\BecomeSellerRequest;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;

final class SellerOnboardingController extends Controller
{
    public function store(
        BecomeSellerRequest $request,
        BecomeSellerAction $action
    ): JsonResponse {
        return response()->json(
            $action->execute($request->user(), $request->validated()),
            201
        );
    }
}
