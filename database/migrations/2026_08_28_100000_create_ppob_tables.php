<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ppob_operators', function (Blueprint $table): void {
            $table->id();
            $table->string('name', 120);
            $table->string('slug', 120)->unique();
            $table->string('category', 40)->index(); // pulsa | data | token-listrik | tagihan | internet | voucher
            $table->string('brand', 120)->nullable();
            $table->string('operator_prefix', 120)->nullable(); // ddi prefixes for pulsa/data
            $table->string('provider_name', 120)->default('IAK');
            $table->string('icon_url', 500)->nullable();
            $table->boolean('is_active')->default(true)->index();
            $table->foreignUuid('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignUuid('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes()->index();
            $table->index(['category', 'is_active'], 'ppob_operators_category_active_index');
        });

        Schema::create('ppob_products', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('operator_id')->nullable()->constrained('ppob_operators')->nullOnDelete();
            $table->string('category', 40)->index(); // pulsa | data | token-listrik | tagihan | internet | voucher ...
            $table->string('product_type', 20)->default('prepaid')->index(); // prepaid | postpaid
            $table->string('provider_product_code', 80)->unique();
            $table->string('name', 160);
            $table->string('brand', 120)->nullable();
            $table->string('nominal', 80)->nullable(); // e.g. "10.000", "Rp 100.000"
            $table->decimal('provider_price', 15, 2)->default(0);
            $table->decimal('admin_fee', 15, 2)->default(0);
            $table->decimal('commission', 15, 2)->default(0);
            $table->decimal('margin', 15, 2)->default(0)->index();
            $table->decimal('selling_price', 15, 2)->default(0)->index();
            $table->string('status', 20)->default('active')->index(); // active | inactive
            $table->boolean('is_available')->default(true)->index();
            $table->string('icon_url', 500)->nullable();
            $table->json('metadata')->nullable();
            $table->boolean('is_active')->default(true)->index();
            $table->foreignUuid('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignUuid('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes()->index();
            $table->index(['category', 'status', 'is_available'], 'ppob_products_category_status_index');
            $table->index(['operator_id', 'category'], 'ppob_products_operator_category_index');
        });

        Schema::create('ppob_pricing_rules', function (Blueprint $table): void {
            $table->id();
            $table->string('level', 20)->index(); // global | category | operator | product
            $table->string('category', 40)->nullable()->index();
            $table->foreignId('operator_id')->nullable()->constrained('ppob_operators')->nullOnDelete();
            $table->foreignId('product_id')->nullable()->constrained('ppob_products')->nullOnDelete();
            $table->string('margin_type', 20)->default('fixed'); // fixed | percentage
            $table->decimal('margin_value', 15, 2)->default(0);
            $table->string('admin_fee_type', 20)->default('fixed'); // fixed | percentage
            $table->decimal('admin_fee_value', 15, 2)->default(0);
            $table->string('commission_type', 20)->default('fixed'); // fixed | percentage
            $table->decimal('commission_value', 15, 2)->default(0);
            $table->decimal('min_selling_price', 15, 2)->nullable();
            $table->decimal('max_selling_price', 15, 2)->nullable();
            $table->integer('priority')->default(0)->index();
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true)->index();
            $table->foreignUuid('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignUuid('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes()->index();
            $table->index(['level', 'is_active', 'priority'], 'ppob_pricing_rules_lookup_index');
        });

        Schema::create('ppob_transactions', function (Blueprint $table): void {
            $table->id();
            // reference_id is the unique idempotency key sent to IAK as ref_id
            $table->string('reference_id', 100)->unique();
            $table->foreignUuid('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('operator_id')->nullable()->constrained('ppob_operators')->nullOnDelete();
            $table->foreignId('product_id')->nullable()->constrained('ppob_products')->nullOnDelete();
            $table->string('provider_product_code', 80)->nullable();
            $table->string('product_name', 200)->nullable();
            $table->string('category', 40)->index();
            $table->string('product_type', 20)->default('prepaid');
            $table->string('customer_id', 120); // phone / meter / subscriber id
            $table->string('customer_name', 160)->nullable();
            $table->decimal('bill_amount', 15, 2)->nullable();
            $table->decimal('provider_price', 15, 2)->default(0);
            $table->decimal('admin_fee', 15, 2)->default(0);
            $table->decimal('commission', 15, 2)->default(0);
            $table->decimal('margin', 15, 2)->default(0);
            $table->decimal('revenue', 15, 2)->default(0);
            $table->decimal('net_profit', 15, 2)->default(0);
            $table->decimal('total_amount', 15, 2)->default(0);
            $table->string('status', 20)->default('pending')->index(); // pending|processing|success|failed|expired|refunded
            $table->string('provider_status', 20)->nullable();
            $table->string('provider_message', 255)->nullable();
            $table->string('tr_id', 40)->nullable()->index();
            $table->string('sn', 500)->nullable();
            $table->string('pin', 255)->nullable();
            $table->json('provider_raw_response')->nullable();
            $table->string('callback_signature', 80)->nullable();
            $table->timestamp('paid_at')->nullable()->index();
            $table->timestamp('completed_at')->nullable()->index();
            $table->timestamp('expires_at')->nullable();
            $table->timestamp('cancelled_at')->nullable();
            $table->json('metadata')->nullable();
            $table->boolean('is_active')->default(true)->index();
            $table->foreignUuid('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignUuid('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes()->index();
            $table->index(['user_id', 'status', 'created_at'], 'ppob_transactions_user_status_index');
            $table->index(['product_id', 'created_at'], 'ppob_transactions_product_created_index');
            $table->index(['status', 'created_at'], 'ppob_transactions_status_created_index');
        });

        Schema::create('ppob_transaction_logs', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('ppob_transaction_id')->nullable()->constrained('ppob_transactions')->nullOnDelete();
            $table->string('reference_id', 100)->index();
            $table->string('action', 40)->index(); // top_up | check_status | inquiry | payment | callback
            $table->string('direction', 20)->index(); // outgoing | incoming
            $table->json('request_payload')->nullable();
            $table->json('response_payload')->nullable();
            $table->integer('http_status')->nullable();
            $table->string('provider_status', 20)->nullable();
            $table->string('provider_message', 255)->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->timestamps();
            $table->index(['ppob_transaction_id', 'action'], 'ppob_transaction_logs_tx_action_index');
        });

        Schema::create('ppob_inquiries', function (Blueprint $table): void {
            $table->id();
            $table->string('reference_id', 100)->index();
            $table->foreignUuid('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('operator_id')->nullable()->constrained('ppob_operators')->nullOnDelete();
            $table->string('product_code', 80)->nullable();
            $table->string('category', 40)->index();
            $table->string('customer_id', 120)->index();
            $table->string('tr_id', 40)->nullable()->index();
            $table->string('customer_name', 160)->nullable();
            $table->string('customer_no', 120)->nullable();
            $table->decimal('bill_amount', 15, 2)->nullable();
            $table->decimal('admin_charge', 15, 2)->nullable();
            $table->string('admin_charge_message', 255)->nullable();
            $table->json('detail')->nullable();
            $table->string('status', 20)->default('active')->index(); // active | expired | paid
            $table->timestamp('expires_at')->nullable()->index();
            $table->timestamps();
            $table->softDeletes()->index();
            $table->index(['user_id', 'status'], 'ppob_inquiries_user_status_index');
        });

        Schema::create('ppob_finance_entries', function (Blueprint $table): void {
            $table->id();
            $table->string('source_type', 60)->index(); // ppob_transaction
            $table->string('source_id', 40);
            $table->foreignId('ppob_transaction_id')->nullable()->constrained('ppob_transactions')->nullOnDelete();
            $table->string('reference_id', 100)->index();
            $table->string('transaction_type', 40)->index(); // revenue | provider_cost | admin_fee | commission | margin | net_profit
            $table->string('title', 160);
            $table->text('description')->nullable();
            $table->decimal('amount', 15, 2);
            $table->string('status', 30)->default('posted')->index();
            $table->timestamp('occurred_at')->index();
            $table->json('metadata')->nullable();
            $table->boolean('is_active')->default(true)->index();
            $table->foreignUuid('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignUuid('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes()->index();
            $table->index(['source_type', 'source_id'], 'ppob_finance_entries_source_index');
            $table->unique(['source_type', 'source_id', 'transaction_type'], 'ppob_finance_entries_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ppob_finance_entries');
        Schema::dropIfExists('ppob_inquiries');
        Schema::dropIfExists('ppob_transaction_logs');
        Schema::dropIfExists('ppob_transactions');
        Schema::dropIfExists('ppob_pricing_rules');
        Schema::dropIfExists('ppob_products');
        Schema::dropIfExists('ppob_operators');
    }
};
