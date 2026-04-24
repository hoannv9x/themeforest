<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('mini_game_predictions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->date('prediction_date');
            $table->json('numbers');
            $table->string('source', 20)->default('user');
            $table->string('predictor_name')->nullable();
            $table->unsignedInteger('hit_count')->default(0);
            $table->timestamp('evaluated_at')->nullable();
            $table->timestamps();

            $table->unique(['user_id', 'prediction_date']);
            $table->unique(['prediction_date', 'source', 'predictor_name'], 'mgp_pred_date_src_pred_name_uq');
            $table->index(['prediction_date', 'source']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('mini_game_predictions');
    }
};
