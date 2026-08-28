<?php

declare(strict_types=1);

namespace App\Domains\Engagement\Gaming\Application\Services;

use App\Domains\Engagement\Gaming\Domain\Repositories\GameSessionRepositoryInterface;
use Illuminate\Support\Facades\DB;

/**
 * Orchestrates a game session report:
 *  1. Validate the result server-side (never trust the client score).
 *  2. Enforce anti-cheat (duplicate session, daily cap).
 *  3. Persist the session (unique on user+game+session_id).
 *  4. Report the validated play into the mission/voucher reward system.
 */
final class GameReportService
{
    public function __construct(
        private GameSessionRepositoryInterface $sessions,
        private GameValidationService $validation,
    ) {}

    /**
     * @return array{status: string, reason: ?string, session: array|null}
     */
    public function report(string $userId, array $payload): array
    {
        $gameType = (string) ($payload['game_type'] ?? '');
        $sessionId = (string) ($payload['session_id'] ?? '');

        if (! in_array($gameType, $this->validation->allowedGames(), true)) {
            return ['status' => 'rejected', 'reason' => 'Jenis permainan tidak dikenali.', 'session' => null];
        }

        if ($sessionId === '' || strlen($sessionId) > 120) {
            return ['status' => 'rejected', 'reason' => 'ID sesi tidak valid.', 'session' => null];
        }

        // Anti-cheat: replay of the same session is rejected.
        if ($this->sessions->findByUserTypeAndSession($userId, $gameType, $sessionId)) {
            return ['status' => 'duplicate', 'reason' => 'Sesi permainan sudah pernah dicatat.', 'session' => null];
        }

        // Anti-cheat: daily farming cap.
        if ($this->sessions->countUserSessionsToday($userId, $gameType) >= $this->validation->dailyCap()) {
            return ['status' => 'rejected', 'reason' => 'Batas bermain harian tercapai.', 'session' => null];
        }

        $duration = max(0, (int) ($payload['duration_seconds'] ?? 0));
        $difficulty = ($payload['difficulty'] ?? null) ? (string) $payload['difficulty'] : null;

        if ($gameType === 'arithmetic_kilat') {
            $validation = $this->validation->validateArithmetic(
                is_array($payload['questions'] ?? null) ? $payload['questions'] : [],
                $duration,
            );
        } elseif ($gameType === 'sudoku') {
            $validation = $this->validation->validateSudoku(
                is_array($payload['grid'] ?? null) ? $payload['grid'] : [],
                $duration,
                $difficulty,
            );
        } else {
            return ['status' => 'rejected', 'reason' => 'Jenis permainan tidak dikenali.', 'session' => null];
        }

        $session = DB::transaction(function () use ($userId, $gameType, $sessionId, $difficulty, $payload, $validation, $duration) {
            if (! $validation['accepted']) {
                return $this->sessions->create([
                    'user_id' => $userId,
                    'game_type' => $gameType,
                    'session_id' => $sessionId,
                    'difficulty' => $difficulty,
                    'validation_status' => 'rejected',
                    'validation_reason' => $validation['reason'],
                    'duration_seconds' => (int) ($payload['duration_seconds'] ?? 0),
                    'answers' => $validation['answers'],
                    'created_by' => $userId,
                    'updated_by' => $userId,
                ]);
            }

            $now = now();

            $session = $this->sessions->create([
                'user_id' => $userId,
                'game_type' => $gameType,
                'session_id' => $sessionId,
                'difficulty' => $difficulty,
                'score' => $validation['score'],
                'correct_count' => $validation['correct_count'],
                'total_questions' => $validation['total_questions'],
                'duration_seconds' => $duration,
                'started_at' => isset($payload['started_at']) ? $payload['started_at'] : null,
                'completed_at' => $now,
                'answers' => $validation['answers'],
                'validation_status' => 'accepted',
                'created_by' => $userId,
                'updated_by' => $userId,
            ]);

            // Report the validated play into the mission/voucher reward system.
            $this->reportToMissions($userId, $gameType, $session->score);

            return $session;
        });

        return [
            'status' => $session->validation_status,
            'reason' => $session->validation_reason,
            'session' => [
                'id' => $session->id,
                'game_type' => $session->game_type,
                'session_id' => $session->session_id,
                'score' => $session->score,
                'correct_count' => $session->correct_count,
                'total_questions' => $session->total_questions,
                'duration_seconds' => $session->duration_seconds,
                'difficulty' => $session->difficulty,
                'coins_awarded' => $session->coins_awarded,
            ],
        ];
    }

    private function reportToMissions(string $userId, string $gameType, int $validatedScore): void
    {
        try {
            $missionService = app(\App\Domains\Engagement\Mission\Application\Services\MissionService::class);
            $eventType = 'game.' . $gameType;
            // Only count plays that scored (a completed play), value = 1 play.
            $missionService->recordEvent($userId, $eventType, 1, ['game_type' => $gameType, 'score' => $validatedScore]);
        } catch (\Throwable $e) {
            // A mission-reporting failure must never break the game session save.
            report($e);
        }
    }
}
