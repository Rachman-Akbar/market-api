<?php

declare(strict_types=1);

namespace App\Domains\Admin\GameContent\Application\Services;

use App\Domains\Engagement\Gaming\Infrastructure\Persistence\Models\GameContentModel;
use App\Domains\Engagement\Gaming\Infrastructure\Persistence\Repositories\EloquentGameContentRepository;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

final class AdminGameContentService
{
    public function types(): array
    {
        return EloquentGameContentRepository::SUPPORTED_TYPES;
    }

    public function paginate(array $filters, int $perPage): LengthAwarePaginator
    {
        $query = GameContentModel::query();

        foreach (['game_type', 'difficulty'] as $column) {
            if (isset($filters[$column]) && trim((string) $filters[$column]) !== '') {
                $query->where($column, (string) $filters[$column]);
            }
        }

        if (isset($filters['is_active'])) {
            $query->where('is_active', (bool) $filters['is_active']);
        }

        if (isset($filters['search']) && trim((string) $filters['search']) !== '') {
            $query->where('title', 'like', '%'.trim((string) $filters['search']).'%');
        }

        return $query->orderByDesc('id')->paginate($perPage);
    }

    public function create(array $data, ?string $userId): GameContentModel
    {
        $this->validatePayloadForType($data['game_type'], $data['payload']);

        return GameContentModel::create([
            'game_type' => $data['game_type'],
            'title' => $data['title'],
            'difficulty' => $data['difficulty'] ?? null,
            'payload' => $data['payload'],
            'is_active' => isset($data['is_active']) ? (bool) $data['is_active'] : true,
            'created_by' => $userId,
            'updated_by' => $userId,
        ]);
    }

    public function update(int $id, array $data, ?string $userId): GameContentModel
    {
        $row = GameContentModel::findOrFail($id);
        $this->validatePayloadForType($data['game_type'], $data['payload']);

        $row->forceFill([
            'game_type' => $data['game_type'],
            'title' => $data['title'],
            'difficulty' => $data['difficulty'] ?? $row->difficulty,
            'payload' => $data['payload'],
            'is_active' => isset($data['is_active']) ? (bool) $data['is_active'] : $row->is_active,
            'updated_by' => $userId,
        ])->save();

        return $row->refresh();
    }

    public function delete(int $id, ?string $userId): void
    {
        $row = GameContentModel::findOrFail($id);
        $row->updated_by = $userId;
        $row->save();
        $row->delete();
    }

    /** @return array<string, mixed> */
    public function format(GameContentModel $row): array
    {
        return [
            'id' => $row->id,
            'game_type' => $row->game_type,
            'title' => $row->title,
            'difficulty' => $row->difficulty,
            'is_active' => $row->is_active,
            'items_count' => is_array($row->payload) ? count($row->payload) : 0,
            'payload' => $row->payload,
            'created_at' => optional($row->created_at)->toIso8601String(),
            'updated_at' => optional($row->updated_at)->toIso8601String(),
        ];
    }

    /**
     * Struktur payload diverifikasi per jenis permainan agar admin panel tidak
     * memasukkan data rusak ke database.
     */
    private function validatePayloadForType(string $type, array $payload): void
    {
        $rules = match ($type) {
            'quiz' => [
                'payload' => ['required', 'array', 'min:1'],
                'payload.*.question' => ['required', 'string'],
                'payload.*.options' => ['required', 'array', 'min:2'],
                'payload.*.correct_answer' => ['required', 'string'],
                'payload.*.difficulty' => ['nullable', 'string'],
                'payload.*.explanation' => ['nullable', 'string'],
            ],
            'myth_fact' => [
                'payload' => ['required', 'array', 'min:1'],
                'payload.*.statement' => ['required', 'string'],
                'payload.*.is_fact' => ['required', 'boolean'],
                'payload.*.image_url' => ['nullable', 'string'],
            ],
            'trash_sort' => [
                'payload' => ['required', 'array', 'min:1'],
                'payload.*.name' => ['required', 'string'],
                'payload.*.bin_color' => ['required', 'string'],
                'payload.*.category' => ['nullable', 'string'],
                'payload.*.image_key' => ['nullable', 'string'],
            ],
            'arithmetic_kilat' => [],
            default => ['payload' => ['required', 'array']],
        };

        if ($rules === []) {
            return;
        }

        $validator = Validator::make(['payload' => $payload], $rules);

        if ($validator->fails()) {
            throw ValidationException::withMessages($validator->errors()->toArray());
        }

        if ($type === 'match_card') {
            $cardValidator = Validator::make($payload, [
                'goals' => ['required', 'array', 'min:1'],
                'statements' => ['required', 'array', 'min:1'],
                'goals.*.goal_number' => ['required', 'integer', 'min:1', 'max:17'],
                'goals.*.name' => ['required', 'string'],
                'statements.*.id' => ['required', 'integer'],
                'statements.*.text' => ['required', 'string'],
                'statements.*.goal_id' => ['required', 'integer'],
            ]);

            if ($cardValidator->fails()) {
                throw ValidationException::withMessages($cardValidator->errors()->toArray());
            }
        }
    }
}