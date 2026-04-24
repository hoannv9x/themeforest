<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('mini_game_weekly_scores', function (Blueprint $table) {
            $table->id();
            $table->date('week_start');
            $table->date('week_end');
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->unsignedInteger('correct_count')->default(0);
            $table->boolean('is_winner')->default(false);
            $table->json('meta')->nullable();
            $table->timestamps();

            $table->unique(['week_start', 'week_end', 'user_id']);
            $table->index(['week_start', 'week_end', 'is_winner']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('mini_game_weekly_scores');
    }
};
