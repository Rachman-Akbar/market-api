<?php

declare(strict_types=1);

namespace App\Domains\Seller\Planner\Infrastructure\Persistence\Repositories;

use App\Domains\Seller\Planner\Domain\Entities\Schedule;
use App\Domains\Seller\Planner\Domain\Repositories\ScheduleRepositoryInterface;
use App\Domains\Seller\Planner\Infrastructure\Persistence\Models\ScheduleModel;
use Carbon\Carbon;

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

        if (! empty($filters['type'])) {
            $query->where('type', $filters['type']);
        }

        if (! empty($filters['priority'])) {
            $query->where('priority', $filters['priority']);
        }

        if (! empty($filters['is_completed'])) {
            $query->where('is_completed', $filters['is_completed']);
        }

        if (! empty($filters['from_date'])) {
            $query->where('date', '>=', $filters['from_date']);
        }

        if (! empty($filters['to_date'])) {
            $query->where('date', '<=', $filters['to_date']);
        }

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

    public function create(array $data): Schedule
    {
        $model = ScheduleModel::create($data);

        return $this->toEntity($model);
    }

    public function update(int $id, array $data): Schedule
    {
        $model = ScheduleModel::findOrFail($id);
        $model->update($data);

        return $this->toEntity($model->fresh());
    }

    public function delete(int $id): void
    {
        ScheduleModel::findOrFail($id)->delete();
    }

    public function markComplete(int $id): Schedule
    {
        $model = ScheduleModel::findOrFail($id);
        $model->update([
            'is_completed' => true,
            'completed_at' => now()->toDateTimeString(),
        ]);

        return $this->toEntity($model->fresh());
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
            priority: $model->priority,
            color: $model->color,
            date: $model->date,
            startTime: $model->start_time,
            endTime: $model->end_time,
            isAllDay: (bool) $model->is_all_day,
            isCompleted: (bool) $model->is_completed,
            completedAt: $model->completed_at?->toDateTimeString(),
            metadata: $model->metadata,
            isActive: (bool) $model->is_active,
            createdAt: $model->created_at?->toDateTimeString(),
            updatedAt: $model->updated_at?->toDateTimeString(),
        );
    }
}
