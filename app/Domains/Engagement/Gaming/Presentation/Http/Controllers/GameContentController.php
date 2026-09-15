<?php

declare(strict_types=1);

namespace App\Domains\Engagement\Gaming\Presentation\Http\Controllers;

use App\Domains\Engagement\Gaming\Infrastructure\Persistence\Repositories\EloquentGameContentRepository;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

final class GameContentController extends Controller
{
    public function __construct(
        private EloquentGameContentRepository $contents,
    ) {}

    public function index(Request $request, string $gameType): JsonResponse
    {
        if (! in_array($gameType, EloquentGameContentRepository::SUPPORTED_TYPES, true)) {
            return response()->json([
                'success' => false,
                'message' => 'Jenis konten permainan tidak dikenali.',
                'data' => null,
            ], 422);
        }

        $difficulty = $request->query('difficulty') !== null
            ? (string) $request->query('difficulty')
            : null;

        $rows = $this->contents->contentByType($gameType, $difficulty);

        return response()->json([
            'success' => true,
            'data' => $rows->map(fn ($row) => [
                'id' => $row->id,
                'game_type' => $row->game_type,
                'title' => $row->title,
                'difficulty' => $row->difficulty,
                'payload' => $row->payload,
            ]),
        ]);
    }
}