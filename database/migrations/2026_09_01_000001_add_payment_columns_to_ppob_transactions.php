<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('ppob_transactions', function (Blueprint $table): void {
            // These columns may already have been added by
            // 2026_08_30_111456_add_payment_to_ppob_transactions_table.
            // Guard each column so the migration is idempotent (safe to run
            // even after a partial/duplicate migration attempt).
            if (! Schema::hasColumn('ppob_transactions', 'payment_method')) {
                $table->string('payment_method', 40)->nullable()->after('total_amount')->index();
            }
            if (! Schema::hasColumn('ppob_transactions', 'payment_status')) {
                $table->string('payment_status', 20)->default('unpaid')->after('payment_method')->index();
            }
            if (! Schema::hasColumn('ppob_transactions', 'snap_token')) {
                $table->string('snap_token', 500)->nullable()->after('payment_status');
            }
        });
    }

    public function down(): void
    {
        Schema::table('ppob_transactions', function (Blueprint $table): void {
            $table->dropColumn(['payment_method', 'payment_status', 'snap_token']);
        });
    }
};
