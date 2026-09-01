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
            $table->string('payment_method', 40)->nullable()->after('total_amount')->index();
            $table->string('payment_status', 20)->default('unpaid')->after('payment_method')->index();
            $table->string('snap_token', 500)->nullable()->after('payment_status');
        });
    }

    public function down(): void
    {
        Schema::table('ppob_transactions', function (Blueprint $table): void {
            $table->dropColumn(['payment_method', 'payment_status', 'snap_token']);
        });
    }
};
