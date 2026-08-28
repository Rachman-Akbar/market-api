<?php

declare(strict_types=1);

namespace App\Domains\Engagement\Gaming\Presentation\Http\Controllers;

use App\Domains\Engagement\Gaming\Application\Services\GameReportService;
use App\Domains\Engagement\Gaming\Domain\Repositories\GameSessionRepositoryInterface;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

final class GameController extends Controller
{
    public function __construct(
        private GameReportService $reportService,
        private GameSessionRepositoryInterface $sessions,
    ) {}

    public function report(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'game_type' => ['required', 'string', 'max:40'],
            'session_id' => ['required', 'string', 'max:120'],
            'duration_seconds' => ['required', 'integer', 'min:0', 'max:86400'],
            'difficulty' => ['nullable', 'string', 'max:30'],
            'questions' => ['nullable', 'array'],
            'grid' => ['nullable', 'array'],
        ]);

        $result = $this->reportService->report((string) $request->user()->id, $validated);

        return match ($result['status']) {
            'accepted' => response()->json([
                'success' => true,
                'message' => 'Hasil permainan tervalidasi dan dicatat.',
                'data' => $result['session'],
            ], 201),
            'duplicate' => response()->json([
                'success' => false,
                'message' => $result['reason'],
                'data' => null,
            ], 409),
            default => response()->json([
                'success' => false,
                'message' => $result['reason'],
                'data' => null,
            ], 422),
        };
    }

    public function history(Request $request, string $gameType): JsonResponse
    {
        if (! in_array($gameType, ['arithmetic_kilat', 'sudoku'], true)) {
            return response()->json(['success' => false, 'message' => 'Jenis permainan tidak dikenali.'], 422);
        }

        $paginator = $this->sessions->userSessions(
            (string) $request->user()->id,
            $gameType,
            min(50, max(1, (int) $request->query('per_page', 20))),
        );

        return response()->json([
            'success' => true,
            'data' => $paginator->items(),
            'meta' => [
                'current_page' => $paginator->currentPage(),
                'last_page' => $paginator->lastPage(),
                'per_page' => $paginator->perPage(),
                'total' => $paginator->total(),
            ],
        ]);
    }

    public function stats(Request $request, string $gameType): JsonResponse
    {
        if (! in_array($gameType, ['arithmetic_kilat', 'sudoku'], true)) {
            return response()->json(['success' => false, 'message' => 'Jenis permainan tidak dikenali.'], 422);
        }

        return response()->json([
            'success' => true,
            'data' => $this->sessions->stats((string) $request->user()->id, $gameType),
        ]);
    }

    public function leaderboard(Request $request, string $gameType): JsonResponse
    {
        if (! in_array($gameType, ['arithmetic_kilat', 'sudoku'], true)) {
            return response()->json(['success' => false, 'message' => 'Jenis permainan tidak dikenali.'], 422);
        }

        return response()->json([
            'success' => true,
            'data' => $this->sessions->leaderboard($gameType, min(50, (int) $request->query('limit', 20))),
        ]);
    }
}
