<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('game_contents', function (Blueprint $table): void {
            $table->id();
            $table->string('game_type', 40)->index();
            $table->string('title', 160);
            $table->string('difficulty', 30)->nullable()->index();
            $table->json('payload');
            $table->boolean('is_active')->default(true)->index();
            $table->foreignUuid('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignUuid('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes()->index();
            $table->index(['game_type', 'is_active', 'difficulty'], 'game_contents_game_type_active_index');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('game_contents');
    }
};