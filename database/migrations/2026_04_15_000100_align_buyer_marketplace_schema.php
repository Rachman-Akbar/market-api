<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table): void {
            if (! Schema::hasColumn('users', 'password')) {
                $table->string('password')->nullable()->after('email');
            }

            if (! Schema::hasColumn('users', 'role')) {
                $table->enum('role', ['buyer', 'seller', 'admin'])->default('buyer')->after('password');
            }
        });

        if (! Schema::hasTable('stores')) {
            Schema::create('stores', function (Blueprint $table): void {
                $table->id();
                $table->uuid('user_id')->unique();
                $table->string('name');
                $table->string('slug')->unique();
                $table->text('description')->nullable();
                $table->string('logo')->nullable();
                $table->timestamps();

                $table->foreign('user_id')->references('id')->on('users')->cascadeOnDelete();
            });
        }

        Schema::table('products', function (Blueprint $table): void {
            if (! Schema::hasColumn('products', 'store_id')) {
                $table->foreignId('store_id')->nullable()->after('id');
            }

            if (! Schema::hasColumn('products', 'category_id')) {
                $table->foreignId('category_id')->nullable()->after('store_id');
            }

            if (! Schema::hasColumn('products', 'stock')) {
                $table->unsignedInteger('stock')->default(0)->after('price');
            }

            if (! Schema::hasColumn('products', 'thumbnail')) {
                $table->string('thumbnail')->nullable()->after('stock');
            }
        });

        Schema::table('products', function (Blueprint $table): void {
            if (Schema::hasColumn('products', 'store_id')) {
                $table->foreign('store_id')->references('id')->on('stores')->nullOnDelete();
            }

            if (Schema::hasColumn('products', 'category_id')) {
                $table->foreign('category_id')->references('id')->on('categories')->nullOnDelete();
            }
        });

        Schema::table('product_images', function (Blueprint $table): void {
            if (! Schema::hasColumn('product_images', 'image_url')) {
                $table->string('image_url')->nullable()->after('product_id');
            }
        });

        DB::table('product_images')
            ->whereNull('image_url')
            ->update(['image_url' => DB::raw('url')]);

        Schema::table('cart_items', function (Blueprint $table): void {
            if (! Schema::hasColumn('cart_items', 'quantity')) {
                $table->unsignedBigInteger('quantity')->default(1)->after('product_id');
            }
        });

        DB::table('cart_items')
            ->where('quantity', 1)
            ->update(['quantity' => DB::raw('qty')]);

        Schema::table('orders', function (Blueprint $table): void {
            if (! Schema::hasColumn('orders', 'user_id')) {
                $table->uuid('user_id')->nullable()->after('id');
                $table->foreign('user_id')->references('id')->on('users')->cascadeOnDelete();
                $table->index(['user_id', 'status']);
            }
        });

        DB::table('orders')
            ->whereNull('user_id')
            ->update(['user_id' => DB::raw('buyer_id')]);

        Schema::table('order_items', function (Blueprint $table): void {
            if (! Schema::hasColumn('order_items', 'quantity')) {
                $table->unsignedBigInteger('quantity')->default(1)->after('product_id');
            }
        });

        DB::table('order_items')
            ->where('quantity', 1)
            ->update(['quantity' => DB::raw('qty')]);

        if (! Schema::hasTable('reviews')) {
            Schema::create('reviews', function (Blueprint $table): void {
                $table->id();
                $table->uuid('user_id');
                $table->foreignId('product_id')->constrained('products')->cascadeOnDelete();
                $table->unsignedTinyInteger('rating');
                $table->text('comment')->nullable();
                $table->timestamps();

                $table->foreign('user_id')->references('id')->on('users')->cascadeOnDelete();
                $table->index(['product_id', 'rating']);
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('reviews')) {
            Schema::drop('reviews');
        }

        Schema::table('order_items', function (Blueprint $table): void {
            if (Schema::hasColumn('order_items', 'quantity')) {
                $table->dropColumn('quantity');
            }
        });

        Schema::table('orders', function (Blueprint $table): void {
            if (Schema::hasColumn('orders', 'user_id')) {
                $table->dropForeign(['user_id']);
                $table->dropIndex('orders_user_id_status_index');
                $table->dropColumn('user_id');
            }
        });

        Schema::table('cart_items', function (Blueprint $table): void {
            if (Schema::hasColumn('cart_items', 'quantity')) {
                $table->dropColumn('quantity');
            }
        });

        Schema::table('product_images', function (Blueprint $table): void {
            if (Schema::hasColumn('product_images', 'image_url')) {
                $table->dropColumn('image_url');
            }
        });

        Schema::table('products', function (Blueprint $table): void {
            if (Schema::hasColumn('products', 'category_id')) {
                $table->dropForeign(['category_id']);
                $table->dropColumn('category_id');
            }

            if (Schema::hasColumn('products', 'store_id')) {
                $table->dropForeign(['store_id']);
                $table->dropColumn('store_id');
            }

            if (Schema::hasColumn('products', 'stock')) {
                $table->dropColumn('stock');
            }

            if (Schema::hasColumn('products', 'thumbnail')) {
                $table->dropColumn('thumbnail');
            }
        });

        if (Schema::hasTable('stores')) {
            Schema::drop('stores');
        }

        Schema::table('users', function (Blueprint $table): void {
            if (Schema::hasColumn('users', 'role')) {
                $table->dropColumn('role');
            }

            if (Schema::hasColumn('users', 'password')) {
                $table->dropColumn('password');
            }
        });
    }
};
