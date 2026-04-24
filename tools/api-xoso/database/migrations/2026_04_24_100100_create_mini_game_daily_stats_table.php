<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('mini_game_daily_stats', function (Blueprint $table) {
            $table->id();
            $table->date('prediction_date')->unique();
            $table->unsignedInteger('total_participants')->default(0);
            $table->json('top_numbers')->nullable();
            $table->json('predicted_numbers')->nullable();
            $table->char('leader_number', 2)->nullable();
            $table->unsignedInteger('leader_votes')->default(0);
            $table->json('ai_suggestion')->nullable();
            $table->timestamp('cutoff_at');
            $table->timestamp('finalized_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('mini_game_daily_stats');
    }
};
