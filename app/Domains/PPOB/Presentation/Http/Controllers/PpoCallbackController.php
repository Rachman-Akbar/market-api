<?php

declare(strict_types=1);

namespace App\Domains\PPOB\Presentation\Http\Controllers;

use App\Domains\PPOB\Application\Services\PpoCallbackHandler;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PpoCallbackController extends Controller
{
    public function __construct(
        private PpoCallbackHandler $handler,
    ) {}

    public function handle(Request $request): JsonResponse
    {
        $handled = $this->handler->handle($request->all(), $request->ip());

        return response()->json([
            'success' => $handled,
            'message' => $handled ? 'OK' : 'Ignored',
        ]);
    }
}
