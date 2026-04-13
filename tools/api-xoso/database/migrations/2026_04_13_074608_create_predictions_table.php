<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Sau này track độ chính xác để upsell
        Schema::create('predictions', function (Blueprint $table) {
            $table->id();
            $table->date('date');
            $table->enum('region', ['mb', 'mt', 'mn']);
            $table->json('numbers')->comment('Số dự đoán');
            $table->json('meta')->nullable();
            $table->string('algorithm')->comment('cách bạn tạo ra bộ số dự đoán');
            $table->float('accuracy')->nullable()->comment('Độ chính xác');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('predictions');
    }
};
