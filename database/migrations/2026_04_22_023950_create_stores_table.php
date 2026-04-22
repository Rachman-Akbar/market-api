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
       Schema::create('stores', function (Blueprint $table) {
    $table->uuid('id')->primary();

    $table->uuid('seller_id');

    $table->string('name');

    // GPS (SOURCE OF TRUTH)
    $table->decimal('latitude', 10, 7);
    $table->decimal('longitude', 10, 7);

    // Address Result
    $table->text('formatted_address')->nullable();
    $table->string('city')->nullable();
    $table->string('province')->nullable();

    $table->boolean('is_active')->default(true);

    $table->timestamps();
});
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('stores');
    }
};
