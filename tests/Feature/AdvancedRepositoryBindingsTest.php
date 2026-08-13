<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Domains\Admin\Notification\Domain\Repositories\AdminNotificationRepositoryInterface;
use App\Domains\Communication\Chat\Domain\Repositories\ConversationRepositoryInterface;
use App\Domains\Engagement\Mission\Domain\Repositories\MissionRepositoryInterface;
use App\Domains\Order\Review\Domain\Repositories\ProductReviewRepositoryInterface;
use Tests\TestCase;

final class AdvancedRepositoryBindingsTest extends TestCase
{
    public function test_advanced_repository_interfaces_are_resolvable(): void
    {
        $this->assertNotNull($this->app->make(AdminNotificationRepositoryInterface::class));
        $this->assertNotNull($this->app->make(MissionRepositoryInterface::class));
        $this->assertNotNull($this->app->make(ProductReviewRepositoryInterface::class));
        $this->assertNotNull($this->app->make(ConversationRepositoryInterface::class));
    }
}
