<?php

declare(strict_types=1);

namespace Tests\Feature\Seller;

use App\Domains\Seller\Planner\Domain\Entities\Schedule;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Support\InteractsAsUser;
use Tests\TestCase;

class PlannerKanbanTest extends TestCase
{
    use InteractsAsUser;
    use RefreshDatabase;

    public function test_seller_can_create_a_kanban_card_then_read_the_board(): void
    {
        [$seller, $store] = $this->actingAsSeller();

        $created = $this->postJson('/api/v1/seller/planner', [
            'title' => 'Restock kardus packaging',
            'description' => 'Pesan ulang kardus ke supplier.',
            'date' => now()->addDay()->toDateString(),
            'type' => 'restock',
            'priority' => 'high',
            'assignee' => 'Tim Gudang',
            'label' => 'Operasional',
        ])->assertCreated()->json('data');

        $this->assertSame('todo', $created['status']);
        $this->assertSame('Restock kardus packaging', $created['title']);

        $board = $this->getJson('/api/v1/seller/planner/board')
            ->assertOk()
            ->json('data');

        $ids = collect($board['columns']['todo'])->pluck('id')->all();
        $this->assertContains($created['id'], $ids);
        $this->assertSame($store->id, $board['columns']['todo'][0]['store_id'] ?? null);
        $this->assertSame([], $board['columns']['done'] ?? null, 'done column should start empty');
        $this->assertTrue(isset($board['totals']['todo']));
    }

    public function test_seller_can_move_a_card_between_kanban_columns(): void
    {
        $seller = $this->actingAsRole('seller');
        $store = $this->makeStore($seller);

        $id = $this->postJson('/api/v1/seller/planner', [
            'title' => 'Packing pesanan pagi',
            'date' => now()->toDateString(),
            'type' => 'task',
        ])->assertCreated()->json('data.id');

        $this->patchJson("/api/v1/seller/planner/{$id}/move", [
            'status' => 'in_progress',
            'to_index' => 0,
        ])->assertOk()->assertJsonPath('data.status', 'in_progress');

        $this->patchJson("/api/v1/seller/planner/{$id}/move", [
            'status' => 'done',
            'to_index' => 0,
        ])->assertOk()->assertJsonPath('data.is_completed', true);

        $this->assertDatabaseHas('schedules', [
            'id' => $id,
            'status' => 'done',
            'is_completed' => true,
        ]);
    }

    public function test_seller_can_complete_a_card_with_optional_proof(): void
    {
        $seller = $this->actingAsRole('seller');
        $this->makeStore($seller);

        $id = $this->postJson('/api/v1/seller/planner', [
            'title' => 'Update stok katalog',
            'date' => now()->toDateString(),
            'type' => 'task',
            'status' => Schedule::STATUS_IN_PROGRESS,
        ])->assertCreated()->json('data.id');

        $this->patchJson("/api/v1/seller/planner/{$id}/complete", [
            'note' => 'Semua varian sudah diperbarui.',
            'files' => ['/storage/marketplace/planner/bukti.jpg'],
        ])
            ->assertOk()
            ->assertJsonPath('data.status', Schedule::STATUS_DONE)
            ->assertJsonPath('data.completion_proof.note', 'Semua varian sudah diperbarui.')
            ->assertJsonPath('data.completion_proof.files.0', '/storage/marketplace/planner/bukti.jpg');

        $this->assertDatabaseHas('schedules', [
            'id' => $id,
            'status' => 'done',
            'is_completed' => true,
        ]);
    }

    public function test_seller_can_complete_a_card_without_proof(): void
    {
        $seller = $this->actingAsRole('seller');
        $this->makeStore($seller);

        $id = $this->postJson('/api/v1/seller/planner', [
            'title' => 'Checklist harian',
            'date' => now()->toDateString(),
            'type' => 'task',
        ])->assertCreated()->json('data.id');

        $this->patchJson("/api/v1/seller/planner/{$id}/complete")
            ->assertOk()
            ->assertJsonPath('data.status', Schedule::STATUS_DONE);

        $this->assertDatabaseHas('schedules', [
            'id' => $id,
            'status' => 'done',
            'is_completed' => true,
        ]);
    }

    public function test_admin_can_view_a_store_board(): void
    {
        $admin = $this->actingAsRole('admin');
        $store = $this->makeStore($admin);

        $this->postJson('/api/v1/seller/planner', [
            'title' => 'Tugas admin untuk toko',
            'date' => now()->toDateString(),
            'type' => 'task',
            'store_id' => $store->id,
        ])->assertCreated();

        $board = $this->getJson('/api/v1/seller/planner/board?store_id='.$store->id)
            ->assertOk()
            ->json('data');

        $titles = collect($board['columns']['todo'])->pluck('title')->all();
        $this->assertContains('Tugas admin untuk toko', $titles);
    }

    public function test_schedule_edit_persists_kanban_fields(): void
    {
        $seller = $this->actingAsRole('seller');
        $this->makeStore($seller);

        $id = $this->postJson('/api/v1/seller/planner', [
            'title' => 'Kartu awal',
            'date' => now()->toDateString(),
            'type' => 'task',
        ])->assertCreated()->json('data.id');

        $this->putJson("/api/v1/seller/planner/{$id}", [
            'title' => 'Kartu diedit',
            'assignee' => 'Andi',
            'label' => 'Produk',
            'priority' => 'urgent',
            'status' => Schedule::STATUS_IN_PROGRESS,
        ])->assertOk()
            ->assertJsonPath('data.assignee', 'Andi')
            ->assertJsonPath('data.label', 'Produk')
            ->assertJsonPath('data.status', Schedule::STATUS_IN_PROGRESS);

        $this->assertDatabaseHas('schedules', [
            'id' => $id,
            'title' => 'Kartu diedit',
            'assignee' => 'Andi',
            'label' => 'Produk',
            'status' => 'in_progress',
        ]);
    }
}
