<?php

declare(strict_types=1);

namespace App\Domains\Seller\Planner\Domain\Entities;

final class Schedule
{
    public const STATUS_TODO = 'todo';

    public const STATUS_IN_PROGRESS = 'in_progress';

    public const STATUS_DONE = 'done';

    public const STATUSES = [self::STATUS_TODO, self::STATUS_IN_PROGRESS, self::STATUS_DONE];

    public const RECURRENCE_NONE = 'none';

    public const RECURRENCE_MONTHLY = 'monthly';

    public const RECURRENCE_YEARLY = 'yearly';

    public const RECURRENCES = [self::RECURRENCE_NONE, self::RECURRENCE_MONTHLY, self::RECURRENCE_YEARLY];

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
        public string $recurrence,
        public ?string $startTime,
        public ?string $endTime,
        public bool $isAllDay,
        public string $status,
        public int $position,
        public ?string $assignee,
        public ?string $label,
        public bool $isCompleted,
        public ?string $completedAt,
        public ?array $completionProof,
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

    public function onDate(string $dateString): self
    {
        return new self(
            id: $this->id,
            userId: $this->userId,
            storeId: $this->storeId,
            title: $this->title,
            description: $this->description,
            type: $this->type,
            priority: $this->priority,
            color: $this->color,
            date: $dateString,
            recurrence: $this->recurrence,
            startTime: $this->startTime,
            endTime: $this->endTime,
            isAllDay: $this->isAllDay,
            status: $this->status,
            position: $this->position,
            assignee: $this->assignee,
            label: $this->label,
            isCompleted: $this->isCompleted,
            completedAt: $this->completedAt,
            completionProof: $this->completionProof,
            metadata: $this->metadata,
            isActive: $this->isActive,
            createdAt: $this->createdAt,
            updatedAt: $this->updatedAt,
        );
    }
}
