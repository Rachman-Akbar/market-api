<?php

declare(strict_types=1);

namespace App\Domains\Catalog\Promotion\Domain\Entities;

final class Promotion
{
    public function __construct(
        public ?int $id,
        public ?int $storeId,
        public ?int $promotionPaymentId,
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

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'store_id' => $this->storeId,
            'promotion_payment_id' => $this->promotionPaymentId,
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
}
