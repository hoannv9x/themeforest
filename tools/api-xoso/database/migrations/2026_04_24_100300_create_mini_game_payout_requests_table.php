<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('mini_game_payout_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->date('week_start');
            $table->date('week_end');
            $table->string('bank_name');
            $table->string('bank_account_name');
            $table->string('bank_account_number');
            $table->string('status')->default('submitted');
            $table->text('note')->nullable();
            $table->timestamps();

            $table->unique(['user_id', 'week_start', 'week_end']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('mini_game_payout_requests');
    }
};
