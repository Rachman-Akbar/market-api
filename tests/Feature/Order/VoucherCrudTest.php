<?php

declare(strict_types=1);

namespace Tests\Feature\Order;

use App\Domains\Order\Voucher\Domain\Entities\Voucher;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Support\InteractsAsUser;
use Tests\TestCase;

class VoucherCrudTest extends TestCase
{
    use RefreshDatabase;
    use InteractsAsUser;

    private function payload(array $overrides = []): array
    {
        return array_merge([
            'voucher_scope' => 'platform',
            'code' => 'SALE10',
            'name' => 'Diskon 10%',
            'discount_target' => 'product',
            'discount_type' => 'percentage',
            'discount_value' => 10,
            'min_spend' => 50000,
            'max_discount' => 20000,
            'starts_at' => '2026-08-01 00:00:00',
            'ends_at' => '2026-12-31 23:59:59',
            'usage_limit' => 100,
            'is_active' => 1,
        ], $overrides);
    }

    public function test_admin_can_create_list_update_and_delete_voucher(): void
    {
        $this->actingAsRole('admin');

        $create = $this->postJson('/api/v1/order/vouchers/admin', $this->payload());
        $create->assertCreated();
        $create->assertJsonPath('success', true);
        $id = $create->json('data.id');
        $this->assertNotNull($id);
        $this->assertSame('sale10', Voucher::query()->find($id)->code);

        $this->getJson('/api/v1/order/vouchers/admin/manage/list')
            ->assertOk()
            ->assertJsonPath('success', true);

        $update = $this->putJson("/api/v1/order/vouchers/admin/{$id}", $this->payload([
            'code' => 'SALE20',
            'name' => 'Diskon 20%',
            'discount_value' => 20,
        ]));
        $update->assertOk();
        $this->assertSame('sale20', Voucher::query()->find($id)->code);
        $this->assertSame('Diskon 20%', Voucher::query()->find($id)->name);

        $this->deleteJson("/api/v1/order/vouchers/admin/{$id}")->assertOk();
        $this->assertSoftDeleted('vouchers', ['id' => $id]);
    }

    public function test_create_validates_required_fields(): void
    {
        $this->actingAsRole('admin');

        $this->postJson('/api/v1/order/vouchers/admin', [
            'code' => 'X',
        ])->assertStatus(422);
    }

    public function test_non_admin_cannot_manage_vouchers(): void
    {
        $this->actingAsRole('buyer');

        $this->getJson('/api/v1/order/vouchers/admin/manage/list')->assertForbidden();
        $this->postJson('/api/v1/order/vouchers/admin', $this->payload())->assertForbidden();
    }

    public function test_admin_cannot_create_duplicate_voucher_code(): void
    {
        $this->actingAsRole('admin');

        $this->postJson('/api/v1/order/vouchers/admin', $this->payload(['code' => 'DUP1']))->assertCreated();
        $this->postJson('/api/v1/order/vouchers/admin', $this->payload(['code' => 'DUP1']))->assertStatus(422);
    }
}
