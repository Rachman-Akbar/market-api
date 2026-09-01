<?php

declare(strict_types=1);

namespace App\Domains\Seller\Planner\Application\Services;

use App\Domains\Seller\Planner\Domain\Entities\Schedule;
use App\Domains\Seller\Planner\Domain\Repositories\ScheduleRepositoryInterface;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class ScheduleService
{
    public function __construct(
        private ScheduleRepositoryInterface $repository
    ) {}

    public function getAll(string $userId, array $filters = [], int $perPage = 20, ?int $storeId = null, bool $isAdmin = false): mixed
    {
        if ($isAdmin && $storeId !== null) {
            return $this->repository->getByStore($storeId, $filters, $perPage);
        }

        return $this->repository->getByUser($userId, $filters, $perPage);
    }

    public function getById(int $id, ?string $userId = null, bool $isAdmin = false): ?Schedule
    {
        $schedule = $this->repository->findById($id);

        if (! $schedule) {
            return null;
        }

        if (! $isAdmin && ($userId === null || $schedule->userId !== $userId)) {
            return null;
        }

        return $schedule;
    }

    public function requireEditable(int $id, string $userId, bool $isAdmin): Schedule
    {
        $schedule = $this->getById($id, $userId, $isAdmin);

        if (! $schedule) {
            throw new ModelNotFoundException('Jadwal tidak ditemukan.');
        }

        return $schedule;
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

    public function getGrid(string $userId, int $year, int $month, ?int $storeId = null, bool $isAdmin = false): array
    {
        if ($isAdmin && $storeId !== null) {
            return $this->repository->getGridDataByStore($storeId, $year, $month);
        }

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
                '"'.$schedule->date.'"',
                '"'.$schedule->type.'"',
                '"'.addslashes($schedule->title).'"',
                '"'.$schedule->priority.'"',
                '"'.($schedule->startTime ?? 'Seharian').'"',
                '"'.($schedule->endTime ?? '-').'"',
                '"'.($schedule->isCompleted ? 'Selesai' : 'Aktif').'"',
            ])."\n";
        }

        return $csv;
    }
}
