<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('temp_results', function (Blueprint $table) {
            $table->id();
            $table->date('date');
            $table->enum('region', ['mb', 'mt', 'mn']);
            $table->string('province_code');
            $table->json('raw_data')->nullable();
            $table->boolean('is_complete')->default(false);
            $table->unsignedInteger('attempts')->default(0);
            $table->timestamp('last_fetched_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();

            $table->unique(['date', 'region', 'province_code']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('temp_results');
    }
};

