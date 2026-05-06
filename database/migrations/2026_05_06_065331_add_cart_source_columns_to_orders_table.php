<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('orders', 'source_cart_id')) {
            Schema::table('orders', function (Blueprint $table): void {
                $table->unsignedBigInteger('source_cart_id')
                    ->nullable()
                    ->after('user_id');

                $table->index('source_cart_id');
            });
        }

        if (! Schema::hasColumn('orders', 'source_cart_item_ids')) {
            Schema::table('orders', function (Blueprint $table): void {
                $table->json('source_cart_item_ids')
                    ->nullable()
                    ->after('source_cart_id');
            });
        }

        if (! Schema::hasColumn('orders', 'payment_failed_reason')) {
            Schema::table('orders', function (Blueprint $table): void {
                $table->text('payment_failed_reason')
                    ->nullable()
                    ->after('payment_instructions');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('orders', 'source_cart_item_ids')) {
            Schema::table('orders', function (Blueprint $table): void {
                $table->dropColumn('source_cart_item_ids');
            });
        }

        if (Schema::hasColumn('orders', 'source_cart_id')) {
            Schema::table('orders', function (Blueprint $table): void {
                $table->dropIndex(['source_cart_id']);
                $table->dropColumn('source_cart_id');
            });
        }

        if (Schema::hasColumn('orders', 'payment_failed_reason')) {
            Schema::table('orders', function (Blueprint $table): void {
                $table->dropColumn('payment_failed_reason');
            });
        }
    }
};
