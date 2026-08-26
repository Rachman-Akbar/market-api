<?php

declare(strict_types=1);

namespace App\Domains\Seller\Planner\Domain\Entities;

final class Schedule
{
    public function __construct(
        public ?int $id,
        public string $userId,
        public ?int $storeId,
        public string $title,
        public ?string $description,
        public string $type,
        public string $priority,
        public string $color,
        public string $date,
        public ?string $startTime,
        public ?string $endTime,
        public bool $isAllDay,
        public bool $isCompleted,
        public ?string $completedAt,
        public ?array $metadata,
        public bool $isActive,
        public ?string $createdAt = null,
        public ?string $updatedAt = null,
    ) {}

    public function getDurationInMinutes(): ?int
    {
        if ($this->isAllDay || ! $this->startTime || ! $this->endTime) {
            return null;
        }

        $start = strtotime($this->startTime);
        $end = strtotime($this->endTime);

        return max(0, (int) round(($end - $start) / 60));
    }
}
