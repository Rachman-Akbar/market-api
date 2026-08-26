<?php

declare(strict_types=1);

namespace App\Domains\Seller\Planner\Application\Services;

use App\Domains\Seller\Planner\Domain\Entities\Schedule;
use App\Domains\Seller\Planner\Domain\Repositories\ScheduleRepositoryInterface;

class ScheduleService
{
    public function __construct(
        private ScheduleRepositoryInterface $repository
    ) {}

    public function getAll(string $userId, array $filters = [], int $perPage = 20): mixed
    {
        return $this->repository->getByUser($userId, $filters, $perPage);
    }

    public function getById(int $id): ?Schedule
    {
        return $this->repository->findById($id);
    }

    public function create(array $data): Schedule
    {
        return $this->repository->create($data);
    }

    public function update(int $id, array $data): Schedule
    {
        return $this->repository->update($id, $data);
    }

    public function delete(int $id): void
    {
        $this->repository->delete($id);
    }

    public function markComplete(int $id): Schedule
    {
        return $this->repository->markComplete($id);
    }

    public function getGrid(string $userId, int $year, int $month): array
    {
        return $this->repository->getGridData($userId, $year, $month);
    }

    public function exportToCsv(string $userId, ?string $fromDate = null, ?string $toDate = null): string
    {
        $filters = [];
        if ($fromDate) {
            $filters['from_date'] = $fromDate;
        }
        if ($toDate) {
            $filters['to_date'] = $toDate;
        }

        $schedules = $this->repository->getByDateRange(
            $userId,
            $fromDate ?? now()->startOfYear()->toDateString(),
            $toDate ?? now()->endOfYear()->toDateString()
        );

        $csv = "Tanggal,Jenis,Judul,Prioritas,Waktu Mulai,Waktu Selesai,Status\n";

        foreach ($schedules as $schedule) {
            $csv .= implode(',', [
                '"' . $schedule->date . '"',
                '"' . $schedule->type . '"',
                '"' . addslashes($schedule->title) . '"',
                '"' . $schedule->priority . '"',
                '"' . ($schedule->startTime ?? 'Seharian') . '"',
                '"' . ($schedule->endTime ?? '-') . '"',
                '"' . ($schedule->isCompleted ? 'Selesai' : 'Aktif') . '"',
            ]) . "\n";
        }

        return $csv;
    }
}
