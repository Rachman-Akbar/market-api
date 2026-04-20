<?php

namespace App\Domains\Realtime\Presentation\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;

final class RealtimeController extends Controller
{
    public function integrationGuide(int $orderId): JsonResponse
    {
        return response()->json([
            'message' => 'Realtime snapshots are mirrored from backend events only.',
            'firestore_document' => 'realtime/orders/' . $orderId,
            'client_write_allowed' => false,
        ]);
    }
}
