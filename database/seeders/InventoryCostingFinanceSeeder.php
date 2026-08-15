<?php

declare(strict_types=1);

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

final class InventoryCostingFinanceSeeder extends Seeder
{
    public function run(): void
    {
        $requiredTables = [
            'stores',
            'products',
            'raw_materials',
            'raw_material_stock_movements',
            'product_materials',
            'product_costings',
        ];

        $missingTables = array_values(array_filter(
            $requiredTables,
            static fn (string $table): bool => ! Schema::hasTable($table)
        ));

        if ($missingTables !== []) {
            if ($this->command) {
                $this->command->warn(
                    'InventoryCostingFinanceSeeder dilewati karena migration belum lengkap. Tabel belum ada: '.implode(', ', $missingTables).'. Jalankan php artisan migrate lalu php artisan db:seed.'
                );
            }

            return;
        }

        $now = now();

        foreach (DB::table('stores')->orderBy('id')->limit(12)->get() as $store) {
            foreach ([
                ['RM-BOX', 'Kardus Packaging', 'pcs', 250, 3500],
                ['RM-BUBBLE', 'Bubble Wrap', 'meter', 500, 1800],
                ['RM-LABEL', 'Label Produk', 'pcs', 1000, 450],
            ] as $row) {
                DB::table('raw_materials')->updateOrInsert(
                    ['store_id' => $store->id, 'code' => $row[0]],
                    [
                        'name' => $row[1],
                        'unit' => $row[2],
                        'stock' => $row[3],
                        'minimum_stock' => 20,
                        'average_cost' => $row[4],
                        'is_active' => true,
                        'updated_at' => $now,
                        'created_at' => $now,
                    ]
                );
            }

            foreach (DB::table('raw_materials')->where('store_id', $store->id)->get() as $material) {
                if (! DB::table('raw_material_stock_movements')->where('raw_material_id', $material->id)->exists()) {
                    DB::table('raw_material_stock_movements')->insert([
                        'store_id' => $store->id,
                        'raw_material_id' => $material->id,
                        'type' => 'restock',
                        'quantity_delta' => $material->stock,
                        'balance_after' => $material->stock,
                        'unit_cost' => $material->average_cost,
                        'total_cost' => $material->stock * $material->average_cost,
                        'reference_type' => 'seed',
                        'reference_number' => 'SEED-'.$material->code,
                        'notes' => 'Stok awal testing',
                        'occurred_at' => $now,
                        'created_at' => $now,
                        'updated_at' => $now,
                    ]);
                }
            }

            foreach (DB::table('products')->where('store_id', $store->id)->orderBy('id')->limit(8)->get() as $product) {
                $materials = DB::table('raw_materials')->where('store_id', $store->id)->orderBy('id')->limit(2)->get();
                $materialCost = 0.0;

                foreach ($materials as $index => $material) {
                    $quantity = $index === 0 ? 1.0 : 0.5;
                    $cost = $quantity * (float) $material->average_cost;
                    $materialCost += $cost;

                    DB::table('product_materials')->updateOrInsert(
                        ['product_id' => $product->id, 'raw_material_id' => $material->id],
                        [
                            'quantity' => $quantity,
                            'unit_cost' => $material->average_cost,
                            'total_cost' => $cost,
                            'created_at' => $now,
                            'updated_at' => $now,
                        ]
                    );
                }

                $hpp = $materialCost + 5000 + 2500;
                $margin = 30;
                $price = round($hpp * 1.3, 2);

                DB::table('product_costings')->updateOrInsert(
                    ['product_id' => $product->id],
                    [
                        'store_id' => $store->id,
                        'material_cost' => $materialCost,
                        'labor_cost' => 5000,
                        'overhead_cost' => 2500,
                        'other_cost' => 0,
                        'hpp' => $hpp,
                        'margin_percent' => $margin,
                        'suggested_price' => $price,
                        'selling_price' => $price,
                        'created_at' => $now,
                        'updated_at' => $now,
                    ]
                );
            }
        }

        if (Schema::hasTable('financial_payment_histories') && Schema::hasTable('financial_transactions')) {
            foreach (DB::table('financial_transactions')->whereIn('type', ['payable', 'receivable'])->orderBy('id')->limit(20)->get() as $transaction) {
                $amount = min(
                    (float) $transaction->amount * 0.25,
                    max(0, (float) $transaction->amount - (float) $transaction->paid_amount)
                );

                if ($amount <= 0 || DB::table('financial_payment_histories')->where('financial_transaction_id', $transaction->id)->exists()) {
                    continue;
                }

                DB::table('financial_payment_histories')->insert([
                    'financial_transaction_id' => $transaction->id,
                    'store_id' => $transaction->store_id,
                    'recorded_by' => null,
                    'amount' => $amount,
                    'balance_before' => $transaction->amount,
                    'balance_after' => $transaction->amount - $amount,
                    'payment_method' => 'transfer',
                    'reference_number' => 'PAY-SEED-'.$transaction->id,
                    'notes' => 'Cicilan awal testing',
                    'paid_at' => $now,
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);

                DB::table('financial_transactions')->where('id', $transaction->id)->update([
                    'paid_amount' => $amount,
                    'status' => $amount >= $transaction->amount ? 'paid' : 'partial',
                    'updated_at' => $now,
                ]);
            }
        }
    }
}
