<?php

declare(strict_types=1);

namespace App\Domains\Engagement\Mission\Application\Services;

use App\Domains\Engagement\Mission\Domain\Repositories\MissionRepositoryInterface;
use App\Domains\Engagement\Mission\Infrastructure\Persistence\Models\MissionModel;
use App\Domains\Engagement\Mission\Infrastructure\Persistence\Models\MissionUserProgressModel;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use InvalidArgumentException;

final class MissionService
{
    /**
     * Event types emitted when a user finishes an Android mini-game.
     * Binds them to missions whose event_type is "game_completed" or "any_game"
     * (mis harian: "Main game 3x" dll).
     */
    public const GAME_COMPLETION_EVENTS = [
        'quiz_completed',
        'trash_sort_completed',
        'myth_fact_completed',
        'match_card_completed',
        'clean_river_completed',
        'game.arithmetic_kilat',
        'game.sudoku',
    ];

    public function __construct(private MissionRepositoryInterface $repository) {}

    public function paginate(array $filters, int $perPage, bool $admin): LengthAwarePaginator
    {
        return $this->repository->paginate($filters, $perPage, $admin);
    }

    public function find(int $id): MissionModel
    {
        return $this->repository->find($id) ?? throw new InvalidArgumentException('Misi tidak ditemukan.');
    }

    public function save(array $data, ?int $id): MissionModel
    {
        $model = $id ? $this->find($id) : new MissionModel;
        $code = Str::upper(trim((string) ($data['code'] ?? '')));

        if ($code === '') {
            $code = 'MSN-'.Str::upper(Str::random(8));
        }

        $exists = MissionModel::withTrashed()->where('code', $code)->when($id, fn ($query) => $query->where('id', '!=', $id))->exists();

        if ($exists) {
            throw new InvalidArgumentException('Kode misi sudah digunakan.');
        }

        $model->fill([
            'voucher_id' => $data['voucher_id'] ?? null,
            'name' => trim((string) $data['name']),
            'code' => $code,
            'description' => $data['description'] ?? null,
            'event_type' => $data['event_type'],
            'target_value' => (int) $data['target_value'],
            'conditions' => $data['conditions'] ?? null,
            'starts_at' => $data['starts_at'],
            'ends_at' => $data['ends_at'],
            'is_active' => $data['is_active'] ?? true,
        ]);

        return $this->repository->save($model);
    }

    public function userMissions(string $userId): array
    {
        $missions = MissionModel::query()
            ->active()
            ->where('starts_at', '<=', now())
            ->where('ends_at', '>=', now())
            ->with(['voucher', 'progresses' => fn ($query) => $query->where('user_id', $userId)])
            ->orderBy('ends_at')
            ->get();

        return $missions->map(function (MissionModel $mission) use ($userId): array {
            $progress = $mission->progresses->first() ?? new MissionUserProgressModel([
                'mission_id' => $mission->id,
                'user_id' => $userId,
                'progress_value' => 0,
                'status' => 'in_progress',
            ]);

            return $this->presentUserMission($mission, $progress);
        })->all();
    }

    public function recordEvent(string $userId, string $eventType, int $value = 1, array $metadata = []): void
    {
        $missions = MissionModel::query()
            ->active()
            ->where('starts_at', '<=', now())
            ->where('ends_at', '>=', now())
            ->get()
            ->filter(fn (MissionModel $mission): bool => $this->matchesEvent((string) $mission->event_type, $eventType));

        foreach ($missions as $mission) {
            DB::transaction(function () use ($mission, $userId, $value, $metadata): void {
                $progress = MissionUserProgressModel::query()
                    ->where('mission_id', $mission->id)
                    ->where('user_id', $userId)
                    ->lockForUpdate()
                    ->first();

                if (! $progress) {
                    $progress = new MissionUserProgressModel([
                        'mission_id' => $mission->id,
                        'user_id' => $userId,
                        'progress_value' => 0,
                        'status' => 'in_progress',
                    ]);
                }

                if (in_array($progress->status, ['completed', 'rewarded'], true)) {
                    return;
                }

                $progress->progress_value = min((int) $mission->target_value, (int) $progress->progress_value + max(0, $value));
                $progress->metadata = array_merge($progress->metadata ?? [], $metadata);

                if ($progress->progress_value >= (int) $mission->target_value) {
                    $progress->status = $mission->voucher_id ? 'rewarded' : 'completed';
                    $progress->completed_at = now();

                    if ($mission->voucher_id) {
                        DB::table('user_vouchers')->insertOrIgnore([
                            'user_id' => $userId,
                            'voucher_id' => $mission->voucher_id,
                            'source_type' => 'mission',
                            'source_id' => (string) $mission->id,
                            'status' => 'available',
                            'claimed_at' => null,
                            'created_at' => now(),
                            'updated_at' => now(),
                        ]);
                        $progress->rewarded_at = now();
                        $progress->reward_voucher_id = $mission->voucher_id;
                    }
                }

                $progress->save();
            });
        }
    }

    public function delete(int $id): void
    {
        $this->repository->delete($this->find($id));
    }

    private function matchesEvent(string $missionEvent, string $event): bool
    {
        $missionEvent = strtolower(trim($missionEvent));
        $event = strtolower(trim($event));

        if ($missionEvent === $event) {
            return true;
        }

        // Prefix wildcard: "game.*" matches "game.arithmetic_kilat", "game.sudoku", dsb.
        if (str_ends_with($missionEvent, '.*')) {
            return str_starts_with($event, substr($missionEvent, 0, -1));
        }

        // Suffix wildcard: "*_completed" matches "quiz_completed", "trash_sort_completed", dsb.
        if ($missionEvent === '*_completed') {
            return str_ends_with($event, '_completed');
        }

        // Mis harian "main game" — memadukan seluruh game Android (mini-game + game terverifikasi).
        if (in_array($missionEvent, ['game_completed', 'any_game'], true)) {
            return in_array($event, self::GAME_COMPLETION_EVENTS, true);
        }

        return false;
    }

    /**
     * @return array<string, string> event_type => label untuk pilihan admin.
     */
    public function supportedEventTypes(): array
    {
        return [
            'game_completed' => 'Main game (semua game Android)',
            'game.*' => 'Game terverifikasi server (arithmetic_kilat & sudoku)',
            'quiz_completed' => 'Quiz selesai',
            'trash_sort_completed' => 'Trash Sort selesai',
            'myth_fact_completed' => 'Myth & Fact selesai',
            'match_card_completed' => 'Match Card selesai',
            'clean_river_completed' => 'Clean River selesai',
            'order_completed' => 'Pesanan selesai',
            'review_submitted' => 'Review dikirim',
            'wishlist_added' => 'Wishlist ditambahkan',
            'login' => 'Login',
            'product_purchased' => 'Produk dibeli',
            'purchase_amount' => 'Total belanja',
        ];
    }

    private function presentUserMission(MissionModel $mission, MissionUserProgressModel $progress): array
    {
        $rewardUnlocked = in_array($progress->status, ['completed', 'rewarded'], true);

        return [
            'id' => $mission->id,
            'name' => $mission->name,
            'code' => $mission->code,
            'description' => $mission->description,
            'event_type' => $mission->event_type,
            'target_value' => (int) $mission->target_value,
            'progress_value' => (int) $progress->progress_value,
            'progress_percent' => min(100, round(((int) $progress->progress_value / max(1, (int) $mission->target_value)) * 100, 2)),
            'status' => $progress->status,
            'starts_at' => $mission->starts_at?->toIso8601String(),
            'ends_at' => $mission->ends_at?->toIso8601String(),
            'voucher_locked' => (bool) $mission->voucher && ! $rewardUnlocked,
            'voucher' => $mission->voucher ? [
                'id' => $mission->voucher->id,
                'code' => $rewardUnlocked ? $mission->voucher->code : null,
                'name' => $mission->voucher->name,
                'discount_type' => $mission->voucher->discount_type,
                'discount_value' => (float) $mission->voucher->discount_value,
            ] : null,
        ];
    }
}
