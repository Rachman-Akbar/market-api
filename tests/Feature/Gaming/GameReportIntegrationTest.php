<?php

declare(strict_types=1);

namespace Tests\Feature\Gaming;

use App\Domains\Engagement\Gaming\Application\Services\GameReportService;
use App\Domains\Engagement\Gaming\Infrastructure\Persistence\Models\GameSessionModel;
use App\Domains\Identity\User\Domain\Entities\User;
use Tests\IntegrationTestCase;

class GameReportIntegrationTest extends IntegrationTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->cleanupSession();
    }

    protected function tearDown(): void
    {
        $this->cleanupSession();
        parent::tearDown();
    }

    protected function cleanupSession(): void
    {
        $user = User::first();
        if (!$user) {
            return;
        }

        GameSessionModel::where('user_id', $user->id)
            ->whereIn('game_type', ['arithmetic_kilat', 'sudoku'])
            ->forceDelete();
    }

    public function test_valid_arithmetic_is_accepted_with_server_score(): void
    {
        $user = User::first();
        $this->assertNotNull($user);

        $svc = app(GameReportService::class);
        $qs = $this->questions();

        $res = $svc->report((string) $user->id, [
            'game_type' => 'arithmetic_kilat',
            'session_id' => 'AR-INT-001',
            'duration_seconds' => 30,
            'questions' => $qs,
        ]);

        $this->assertSame('accepted', $res['status']);
        $this->assertSame(50, $res['session']['score']);
    }

    public function test_duplicate_session_is_rejected(): void
    {
        $user = User::first();
        $this->assertNotNull($user);

        $svc = app(GameReportService::class);
        $qs = $this->questions();

        $svc->report((string) $user->id, [
            'game_type' => 'arithmetic_kilat',
            'session_id' => 'AR-INT-002',
            'duration_seconds' => 30,
            'questions' => $qs,
        ]);

        $dup = $svc->report((string) $user->id, [
            'game_type' => 'arithmetic_kilat',
            'session_id' => 'AR-INT-002',
            'duration_seconds' => 30,
            'questions' => $qs,
        ]);

        $this->assertSame('duplicate', $dup['status']);
    }

    public function test_wrong_answer_is_not_trusted_and_recomputed(): void
    {
        $user = User::first();
        $this->assertNotNull($user);

        $svc = app(GameReportService::class);
        $qs = [
            ['operand_a' => 2, 'operator' => '+', 'operand_b' => 3, 'user_answer' => 100],
            ['operand_a' => 5, 'operator' => '*', 'operand_b' => 4, 'user_answer' => 20],
            ['operand_a' => 1, 'operator' => '+', 'operand_b' => 1, 'user_answer' => 2],
        ];

        $res = $svc->report((string) $user->id, [
            'game_type' => 'arithmetic_kilat',
            'session_id' => 'AR-INT-003',
            'duration_seconds' => 30,
            'questions' => $qs,
        ]);

        $this->assertSame(2, $res['session']['correct_count']);
        $this->assertSame(20, $res['session']['score']);
    }

    public function test_impossible_duration_is_rejected(): void
    {
        $user = User::first();
        $this->assertNotNull($user);

        $svc = app(GameReportService::class);

        $res = $svc->report((string) $user->id, [
            'game_type' => 'arithmetic_kilat',
            'session_id' => 'AR-INT-004',
            'duration_seconds' => 1,
            'questions' => $this->questions(),
        ]);

        $this->assertSame('rejected', $res['status']);
    }

    public function test_valid_sudoku_is_accepted(): void
    {
        $user = User::first();
        $this->assertNotNull($user);

        $svc = app(GameReportService::class);

        $res = $svc->report((string) $user->id, [
            'game_type' => 'sudoku',
            'session_id' => 'SD-INT-001',
            'duration_seconds' => 60,
            'difficulty' => 'Sedang',
            'grid' => $this->validGrid(),
        ]);

        $this->assertSame('accepted', $res['status']);
        $this->assertSame(500, $res['session']['score']);
    }

    public function test_invalid_sudoku_is_rejected(): void
    {
        $user = User::first();
        $this->assertNotNull($user);

        $svc = app(GameReportService::class);
        $bad = $this->validGrid();
        $bad[0] = $bad[1];

        $res = $svc->report((string) $user->id, [
            'game_type' => 'sudoku',
            'session_id' => 'SD-INT-002',
            'duration_seconds' => 60,
            'grid' => $bad,
        ]);

        $this->assertSame('rejected', $res['status']);
    }

    private function questions(): array
    {
        $out = [];
        foreach ([[2, '+', 3], [5, '*', 4], [20, '-', 7], [10, '/', 2], [3, '+', 9]] as [$a, $op, $b]) {
            $out[] = [
                'operand_a' => $a,
                'operator' => $op,
                'operand_b' => $b,
                'user_answer' => match ($op) {
                    '+' => $a + $b,
                    '-' => $a - $b,
                    '*' => $a * $b,
                    '/' => intdiv($a, $b),
                },
            ];
        }

        return $out;
    }

    private function validGrid(): array
    {
        return [
            5,3,4,6,7,8,9,1,2,
            6,7,2,1,9,5,3,4,8,
            1,9,8,3,4,2,5,6,7,
            8,5,9,7,6,1,4,2,3,
            4,2,6,8,5,3,7,9,1,
            7,1,3,9,2,4,8,5,6,
            9,6,1,5,3,7,2,8,4,
            2,8,7,4,1,9,6,3,5,
            3,4,5,2,8,6,1,7,9,
        ];
    }
}
