<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('ppob_transactions', function (Blueprint $table) {
            $table->string('payment_method', 50)->nullable()->after('total_amount');
            $table->string('payment_status', 30)->default('pending')->index()->after('payment_method');
            $table->text('midtrans_snap_token')->nullable()->after('payment_status');
            $table->string('midtrans_transaction_id', 100)->nullable()->after('midtrans_snap_token');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('ppob_transactions', function (Blueprint $table) {
            $table->dropColumn(['payment_method', 'payment_status', 'midtrans_snap_token', 'midtrans_transaction_id']);
        });
    }
};
