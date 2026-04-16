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
        if (Schema::hasTable('product_categories')) {
            Schema::drop('product_categories');
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (! Schema::hasTable('product_categories')) {
            Schema::create('product_categories', function (Blueprint $table): void {
                $table->foreignId('product_id')->constrained('products')->cascadeOnDelete();
                $table->foreignId('category_id')->constrained('categories')->cascadeOnDelete();

                $table->primary(['product_id', 'category_id']);
            });
        }
    }
};
