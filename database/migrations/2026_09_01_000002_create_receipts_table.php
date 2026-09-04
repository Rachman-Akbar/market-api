<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('receipts', function (Blueprint $table): void {
            $table->id();
            // Unique, human-friendly receipt number e.g. RCT-20260901-000001
            $table->string('receipt_number', 64)->unique();
            $table->foreignUuid('user_id')->constrained('users')->cascadeOnDelete();
            // Polymorphic source: marketplace order or ppob transaction
            $table->string('source_type', 40)->index(); // order | ppob_transaction
            $table->string('source_id', 64)->index();
            $table->string('transaction_reference', 100)->nullable();
            $table->string('receipt_type', 30)->default('digital'); // digital | order
            $table->string('product_name', 200)->nullable();
            $table->string('category', 40)->nullable();
            $table->string('customer_id', 120)->nullable();
            $table->string('customer_name', 160)->nullable();
            $table->decimal('subtotal', 15, 2)->default(0);
            $table->decimal('admin_fee', 15, 2)->default(0);
            $table->decimal('discount', 15, 2)->default(0);
            $table->decimal('total', 15, 2)->default(0);
            $table->string('payment_method', 50)->nullable();
            $table->string('payment_status', 30)->default('pending');
            $table->string('transaction_status', 30)->default('pending');
            $table->timestamp('paid_at')->nullable();
            // Email side-effect tracking (idempotent sending)
            $table->timestamp('email_sent_at')->nullable();
            $table->string('email_status', 30)->default('none'); // none | sent | failed
            $table->string('email_message_id', 160)->nullable();
            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('updated_at')->useCurrent()->useCurrentOnUpdate();
            $table->softDeletes();
            // Idempotency: one receipt per source
            $table->unique(['source_type', 'source_id'], 'receipts_source_unique');
            $table->index(['user_id', 'created_at'], 'receipts_user_created_index');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('receipts');
    }
};
