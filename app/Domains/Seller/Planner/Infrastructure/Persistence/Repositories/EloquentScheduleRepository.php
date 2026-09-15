<?php

declare(strict_types=1);

namespace App\Domains\Seller\Planner\Infrastructure\Persistence\Repositories;

use App\Domains\Seller\Planner\Domain\Entities\Schedule;
use App\Domains\Seller\Planner\Domain\Repositories\ScheduleRepositoryInterface;
use App\Domains\Seller\Planner\Infrastructure\Persistence\Models\ScheduleModel;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class EloquentScheduleRepository implements ScheduleRepositoryInterface
{
    public function findById(int $id): ?Schedule
    {
        $model = ScheduleModel::find($id);

        return $model ? $this->toEntity($model) : null;
    }

    public function getByUser(string $userId, array $filters = [], int $perPage = 20): mixed
    {
        $query = ScheduleModel::where('user_id', $userId)->where('is_active', true);

        $this->applyFilters($query, $filters);

        return $query->orderBy('date')->orderBy('start_time')->paginate($perPage);
    }

    public function getByStore(int $storeId, array $filters = [], int $perPage = 20): mixed
    {
        $query = ScheduleModel::where('store_id', $storeId)->where('is_active', true);

        $this->applyFilters($query, $filters);

        return $query->orderBy('date')->orderBy('start_time')->paginate($perPage);
    }

    public function getByDateRange(string $userId, string $fromDate, string $toDate): array
    {
        $models = ScheduleModel::where('user_id', $userId)
            ->where('is_active', true)
            ->where('date', '>=', $fromDate)
            ->where('date', '<=', $toDate)
            ->orderBy('date')
            ->orderBy('start_time')
            ->get();

        return $models->map(fn ($m) => $this->toEntity($m))->all();
    }

    public function getGridData(string $userId, int $year, int $month): array
    {
        $startDate = Carbon::create($year, $month, 1)->startOfMonth()->toDateString();
        $endDate = Carbon::create($year, $month, 1)->endOfMonth()->toDateString();

        $schedules = $this->getByDateRange($userId, $startDate, $endDate);

        return $this->buildGrid($schedules, $year, $month);
    }

    public function getGridDataByStore(int $storeId, int $year, int $month): array
    {
        $startDate = Carbon::create($year, $month, 1)->startOfMonth()->toDateString();
        $endDate = Carbon::create($year, $month, 1)->endOfMonth()->toDateString();

        $models = ScheduleModel::where('store_id', $storeId)
            ->where('is_active', true)
            ->where('date', '>=', $startDate)
            ->where('date', '<=', $endDate)
            ->orderBy('date')
            ->orderBy('start_time')
            ->get();

        return $this->buildGrid($models->map(fn ($m) => $this->toEntity($m))->all(), $year, $month);
    }

    public function getBoard(?string $userId, ?int $storeId, bool $isAdmin, array $filters = []): array
    {
        $query = ScheduleModel::where('is_active', true);

        if ($isAdmin && $storeId !== null) {
            $query->where('store_id', $storeId);
        } else {
            $query->where('user_id', (string) $userId);
        }

        $this->applyFilters($query, $filters);

        $items = $query->orderBy('position')->orderBy('date')->orderBy('start_time')->get();

        $columns = ['todo' => [], 'in_progress' => [], 'done' => []];

        foreach ($items as $model) {
            $status = $model->status;
            if (! isset($columns[$status])) {
                $columns[$status] = [];
            }
            $columns[$status][] = $this->cardShape($model);
        }

        return [
            'columns' => $columns,
            'totals' => array_map('count', $columns),
        ];
    }

    public function create(array $data): Schedule
    {
        if (! array_key_exists('position', $data)) {
            $status = (string) ($data['status'] ?? Schedule::STATUS_TODO);
            $scopeQuery = ScheduleModel::where('is_active', true)->where('status', $status);
            $scopeQuery = $data['store_id'] ?? null
                ? $scopeQuery->where('store_id', (int) $data['store_id'])
                : $scopeQuery->where('user_id', (string) $data['user_id']);
            $data['position'] = ((int) $scopeQuery->max('position')) + 1;
        }

        $model = ScheduleModel::create($data);

        return $this->toEntity($model);
    }

    public function update(int $id, array $data): Schedule
    {
        $model = ScheduleModel::findOrFail($id);

        if (array_key_exists('status', $data)) {
            $data = $this->syncStatusFields($data);
        }

        $model->update($data);

        return $this->toEntity($model->fresh());
    }

    public function delete(int $id): void
    {
        ScheduleModel::findOrFail($id)->delete();
    }

    public function markComplete(int $id, ?array $proof = null): Schedule
    {
        $model = ScheduleModel::findOrFail($id);
        $model->update([
            'status' => Schedule::STATUS_DONE,
            'is_completed' => true,
            'completed_at' => now()->toDateTimeString(),
            'completion_proof' => $proof ?: ['note' => null, 'files' => []],
        ]);

        return $this->toEntity($model->fresh());
    }

    public function moveAndReorder(int $id, string $status, int $toIndex): Schedule
    {
        if (! in_array($status, Schedule::STATUSES, true)) {
            throw new InvalidArgumentException('Status jadwal tidak valid.');
        }

        $toIndex = max(0, (int) $toIndex);

        return DB::transaction(function () use ($id, $status, $toIndex): Schedule {
            $model = ScheduleModel::whereKey($id)->lockForUpdate()->firstOrFail();

            $columnQuery = fn (string $state) => ScheduleModel::where('is_active', true)
                ->when($model->store_id !== null, fn ($query) => $query->where('store_id', $model->store_id))
                ->when($model->store_id === null, fn ($query) => $query->where('user_id', (string) $model->user_id))
                ->where('status', $state)
                ->orderBy('position')
                ->orderBy('id')
                ->get();

            $columns = [];
            foreach (Schedule::STATUSES as $state) {
                $columns[$state] = $columnQuery($state)->all();
            }

            foreach ($columns as $state => $items) {
                $columns[$state] = array_values(array_filter($items, fn ($item) => (int) $item->id !== $id));
            }

            $clampedIndex = min($toIndex, count($columns[$status]));
            array_splice($columns[$status], $clampedIndex, 0, [$model]);

            foreach ($columns as $state => $items) {
                foreach ($items as $index => $item) {
                    $attributes = ['status' => $state, 'position' => $index];
                    if ($state === Schedule::STATUS_DONE) {
                        $attributes['is_completed'] = true;
                        $attributes['completed_at'] = $item->completed_at ?? now()->toDateTimeString();
                    } else {
                        $attributes['is_completed'] = false;
                        $attributes['completed_at'] = null;
                    }
                    ScheduleModel::whereKey($item->id)->update($attributes);
                }
            }

            return $this->toEntity(ScheduleModel::findOrFail($id));
        });
    }

    private function applyFilters($query, array $filters): void
    {
        if (! empty($filters['type'])) {
            $query->where('type', $filters['type']);
        }

        if (! empty($filters['priority'])) {
            $query->where('priority', $filters['priority']);
        }

        if (! empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (array_key_exists('is_completed', $filters) && $filters['is_completed'] !== '' && $filters['is_completed'] !== null) {
            $query->where('is_completed', (bool) $filters['is_completed']);
        }

        if (! empty($filters['from_date'])) {
            $query->where('date', '>=', $filters['from_date']);
        }

        if (! empty($filters['to_date'])) {
            $query->where('date', '<=', $filters['to_date']);
        }
    }

    private function syncStatusFields(array $data): array
    {
        if ((string) $data['status'] === Schedule::STATUS_DONE) {
            $data['is_completed'] = true;
            $data['completed_at'] = $data['completed_at'] ?? now()->toDateTimeString();
        } else {
            $data['is_completed'] = false;
            $data['completed_at'] = null;
        }

        return $data;
    }

    private function buildGrid(array $schedules, int $year, int $month): array
    {
        $startDate = Carbon::create($year, $month, 1)->startOfMonth()->toDateString();
        $endDate = Carbon::create($year, $month, 1)->endOfMonth()->toDateString();

        $grid = [];
        $current = Carbon::parse($startDate);

        while ($current->lte(Carbon::parse($endDate))) {
            $dayStr = $current->toDateString();
            $daySchedules = array_filter($schedules, fn ($s) => $s->date === $dayStr);

            $grid[] = [
                'date' => $dayStr,
                'day_name' => $current->locale('id')->isoFormat('dddd'),
                'schedules' => array_map(fn ($s) => [
                    'id' => $s->id,
                    'title' => $s->title,
                    'type' => $s->type,
                    'status' => $s->status,
                    'priority' => $s->priority,
                    'color' => $s->color,
                    'start_time' => $s->startTime,
                    'end_time' => $s->endTime,
                    'is_all_day' => $s->isAllDay,
                    'is_completed' => $s->isCompleted,
                ], array_values($daySchedules)),
                'count' => count($daySchedules),
            ];

            $current->addDay();
        }

        return [
            'year' => $year,
            'month' => $month,
            'grid' => $grid,
        ];
    }

    private function cardShape(ScheduleModel $model): array
    {
        return [
            'id' => $model->id,
            'user_id' => $model->user_id,
            'store_id' => $model->store_id,
            'title' => $model->title,
            'description' => $model->description,
            'type' => $model->type,
            'status' => $model->status,
            'position' => (int) $model->position,
            'assignee' => $model->assignee,
            'label' => $model->label,
            'priority' => (string) ($model->priority ?: 'normal'),
            'color' => (string) ($model->color ?: '#10B981'),
            'date' => $model->date,
            'start_time' => $model->start_time,
            'end_time' => $model->end_time,
            'is_all_day' => (bool) $model->is_all_day,
            'is_completed' => (bool) $model->is_completed,
            'completed_at' => $model->completed_at?->toDateTimeString(),
            'completion_proof' => $model->completion_proof,
            'created_at' => $model->created_at?->toDateTimeString(),
            'updated_at' => $model->updated_at?->toDateTimeString(),
        ];
    }

    private function toEntity(ScheduleModel $model): Schedule
    {
        return new Schedule(
            id: $model->id,
            userId: $model->user_id,
            storeId: $model->store_id,
            title: $model->title,
            description: $model->description,
            type: $model->type,
            priority: (string) ($model->priority ?: 'normal'),
            color: (string) ($model->color ?: '#10B981'),
            date: $model->date,
            startTime: $model->start_time,
            endTime: $model->end_time,
            isAllDay: (bool) $model->is_all_day,
            status: (string) ($model->status ?: Schedule::STATUS_TODO),
            position: (int) $model->position,
            assignee: $model->assignee,
            label: $model->label,
            isCompleted: (bool) $model->is_completed,
            completedAt: $model->completed_at?->toDateTimeString(),
            completionProof: $model->completion_proof,
            metadata: $model->metadata,
            isActive: (bool) $model->is_active,
            createdAt: $model->created_at?->toDateTimeString(),
            updatedAt: $model->updated_at?->toDateTimeString(),
        );
    }
}
