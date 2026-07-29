<?php

declare(strict_types=1);

namespace App\Domains\Catalog\Promotion\Application\Dtos;

use Illuminate\Support\Str;

final class PromotionData
{
    public function __construct(
        public ?int $id,
        public ?int $storeId,
        public string $name,
        public string $imageUrl,
        public ?string $mobileImageUrl,
        public string $clickAction,
        public ?int $targetId,
        public ?string $targetUrl,
        public int $sortOrder,
        public bool $isActive,
        public string $approvalStatus = 'pending',
        public ?string $rejectionReason = null,
        public ?string $submittedAt = null,
        public ?string $approvedAt = null,
        public ?string $approvedBy = null,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            id: isset($data['id']) ? (int) $data['id'] : null,
            storeId: isset($data['store_id']) && $data['store_id'] !== null ? (int) $data['store_id'] : null,
            name: Str::lower(trim((string) ($data['name'] ?? ''))),
            imageUrl: trim((string) ($data['image_url'] ?? '')),
            mobileImageUrl: self::nullableString($data['mobile_image_url'] ?? null),
            clickAction: (string) ($data['click_action'] ?? 'none'),
            targetId: isset($data['target_id']) && $data['target_id'] !== null ? (int) $data['target_id'] : null,
            targetUrl: self::nullableString($data['target_url'] ?? null),
            sortOrder: (int) ($data['sort_order'] ?? 0),
            isActive: (bool) ($data['is_active'] ?? true),
            approvalStatus: (string) ($data['approval_status'] ?? 'pending'),
            rejectionReason: self::nullableString($data['rejection_reason'] ?? null),
            submittedAt: self::nullableString($data['submitted_at'] ?? null),
            approvedAt: self::nullableString($data['approved_at'] ?? null),
            approvedBy: self::nullableString($data['approved_by'] ?? null),
        );
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'store_id' => $this->storeId,
            'name' => $this->name,
            'image_url' => $this->imageUrl,
            'mobile_image_url' => $this->mobileImageUrl,
            'click_action' => $this->clickAction,
            'target_id' => $this->targetId,
            'target_url' => $this->targetUrl,
            'sort_order' => $this->sortOrder,
            'is_active' => $this->isActive,
            'approval_status' => $this->approvalStatus,
            'rejection_reason' => $this->rejectionReason,
            'submitted_at' => $this->submittedAt,
            'approved_at' => $this->approvedAt,
            'approved_by' => $this->approvedBy,
        ];
    }

    private static function nullableString(mixed $value): ?string
    {
        $value = trim((string) ($value ?? ''));

        return $value === '' ? null : $value;
    }
}
