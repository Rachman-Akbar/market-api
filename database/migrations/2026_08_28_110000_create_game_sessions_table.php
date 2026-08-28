<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('game_sessions', function (Blueprint $table): void {
            $table->id();
            $table->foreignUuid('user_id')->constrained('users')->cascadeOnDelete();
            $table->string('game_type', 40)->index(); // arithmetic_kilat | sudoku
            $table->string('session_id', 120); // client-generated unique id for the game instance
            $table->string('difficulty', 30)->nullable()->index(); // Mudah | Sedang | Sulit (for sudoku)
            $table->unsignedInteger('score')->default(0);
            $table->unsignedInteger('correct_count')->default(0);
            $table->unsignedInteger('total_questions')->default(0);
            $table->unsignedInteger('duration_seconds')->default(0);
            $table->timestamp('started_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->json('answers')->nullable(); // validated per-question results / sudoku grid
            $table->string('validation_status', 30)->default('accepted')->index(); // accepted | rejected | duplicate
            $table->string('validation_reason', 255)->nullable();
            $table->unsignedInteger('coins_awarded')->nullable();
            $table->boolean('is_active')->default(true)->index();
            $table->foreignUuid('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignUuid('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            // Anti-cheat: a game instance may only be recorded once per user+type.
            $table->unique(['user_id', 'game_type', 'session_id'], 'game_sessions_user_type_session_unique');
            $table->index(['user_id', 'game_type', 'created_at'], 'game_sessions_user_type_created_index');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('game_sessions');
    }
};
