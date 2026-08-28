<?php

declare(strict_types=1);

namespace App\Domains\Engagement\Gaming\Application\Services;

/**
 * Server-side game result validation. The client's reported score is never
 * trusted; correctness is recomputed here from the submitted data.
 *
 * Anti-cheat enforced:
 *  - Duplicate session (handled by caller via unique constraint)
 *  - Impossible duration for the number of questions
 *  - Score out of bounds
 *  - Malformed / non-numeric arithmetic operands
 *  - Invalid / incomplete sudoku grid
 */
final class GameValidationService
{
    private const MIN_SECONDS_PER_QUESTION = 1;

    private const SUDOKU_POINTS = 500;

    private const SUDOKU_MIN_SECONDS = [
        'Mudah' => 15,
        'Sedang' => 30,
        'Sulit' => 60,
    ];

    /**
     * @return array{accepted: bool, reason: ?string, correct_count: int, total_questions: int, score: int, answers: array}
     */
    public function validateArithmetic(array $questions, int $durationSeconds): array
    {
        $total = count($questions);

        if ($total < 1) {
            return $this->reject('Tidak ada soal yang dikirim.');
        }

        if ($total > 100) {
            return $this->reject('Jumlah soal melebihi batas.');
        }

        // Impossible duration: each question needs at least ~1 second.
        $minDuration = max(3, $total * self::MIN_SECONDS_PER_QUESTION);
        if ($durationSeconds < $minDuration) {
            return $this->reject("Durasi terlalu singkat untuk {$total} soal.");
        }

        $correct = 0;
        $validated = [];

        foreach ($questions as $i => $q) {
            $a = $q['operand_a'] ?? null;
            $b = $q['operand_b'] ?? null;
            $op = (string) ($q['operator'] ?? '');
            $userAnswer = $q['user_answer'] ?? $q['answer'] ?? null;

            $isCorrect = false;

            if (is_numeric($a) && is_numeric($b) && is_numeric($userAnswer) && in_array($op, ['+', '-', '*', '/'], true)) {
                $expected = $this->safeEvaluate((float) $a, (float) $b, $op);
                // tolerance for float division
                $isCorrect = $expected !== null && abs($userAnswer - $expected) < 0.0001;
            }

            if ($isCorrect) {
                $correct++;
            }

            $validated[] = [
                'index' => $i,
                'operand_a' => $a,
                'operator' => $op,
                'operand_b' => $b,
                'expected' => $isCorrect ? null : $this->safeEvaluate((float) $a, (float) $b, $op),
                'user_answer' => $userAnswer,
                'correct' => $isCorrect,
            ];
        }

        $score = $correct * 10;

        return [
            'accepted' => true,
            'reason' => null,
            'correct_count' => $correct,
            'total_questions' => $total,
            'score' => $score,
            'answers' => $validated,
        ];
    }

    /**
     * @return array{accepted: bool, reason: ?string, correct_count: int, total_questions: int, score: int, answers: array}
     */
    public function validateSudoku(array $grid, int $durationSeconds, ?string $difficulty = null): array
    {
        if (count($grid) !== 81) {
            return $this->reject('Grid Sudoku harus berisi 81 sel.');
        }

        foreach ($grid as $cell) {
            if (! is_numeric($cell) || (int) $cell < 1 || (int) $cell > 9) {
                return $this->reject('Grid Sudoku mengandung nilai tidak valid.');
            }
        }

        $values = array_map('intval', $grid);

        // Validity check: rows, columns, 3x3 boxes must each have 1-9 exactly once.
        for ($r = 0; $r < 9; $r++) {
            $seen = [];
            for ($c = 0; $c < 9; $c++) {
                $v = $values[$r * 9 + $c];
                if (isset($seen[$v])) {
                    return $this->reject('Solusi Sudoku tidak valid (baris).');
                }
                $seen[$v] = true;
            }
        }

        for ($c = 0; $c < 9; $c++) {
            $seen = [];
            for ($r = 0; $r < 9; $r++) {
                $v = $values[$r * 9 + $c];
                if (isset($seen[$v])) {
                    return $this->reject('Solusi Sudoku tidak valid (kolom).');
                }
                $seen[$v] = true;
            }
        }

        for ($br = 0; $br < 3; $br++) {
            for ($bc = 0; $bc < 3; $bc++) {
                $seen = [];
                for ($r = $br * 3; $r < $br * 3 + 3; $r++) {
                    for ($c = $bc * 3; $c < $bc * 3 + 3; $c++) {
                        $v = $values[$r * 9 + $c];
                        if (isset($seen[$v])) {
                            return $this->reject('Solusi Sudoku tidak valid (kotak).');
                        }
                        $seen[$v] = true;
                    }
                }
            }
        }

        // Anti-cheat: reasonable solve time based on difficulty.
        $minSeconds = self::SUDOKU_MIN_SECONDS[$difficulty] ?? 15;
        if ($durationSeconds < $minSeconds) {
            return $this->reject("Solusi Sudoku terlalu cepat untuk tingkat {$difficulty}.");
        }

        return [
            'accepted' => true,
            'reason' => null,
            'correct_count' => 81,
            'total_questions' => 81,
            'score' => self::SUDOKU_POINTS,
            'answers' => $values,
        ];
    }

    public function allowedGames(): array
    {
        return ['arithmetic_kilat', 'sudoku'];
    }

    public function dailyCap(): int
    {
        return 50;
    }

    private function safeEvaluate(float $a, float $b, string $op): ?float
    {
        return match ($op) {
            '+' => $a + $b,
            '-' => $a - $b,
            '*' => $a * $b,
            '/' => $b == 0 ? null : $a / $b,
            default => null,
        };
    }

    private function reject(string $reason): array
    {
        return [
            'accepted' => false,
            'reason' => $reason,
            'correct_count' => 0,
            'total_questions' => 0,
            'score' => 0,
            'answers' => [],
        ];
    }
}
