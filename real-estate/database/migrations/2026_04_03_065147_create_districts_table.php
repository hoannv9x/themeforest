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
        Schema::create('districts', function (Blueprint $table) {
            $table->id()->comment('ID định danh duy nhất cho mỗi quận/huyện');
            $table->foreignId('city_id')->constrained('cities')->onDelete('cascade')->comment('Khóa ngoại liên kết đến thành phố');
            $table->string('name')->comment('Tên của quận/huyện');
            $table->string('slug')->comment('Chuỗi định danh cho URL');
            $table->timestamps();

            // Đảm bảo tên và slug là duy nhất trong phạm vi một thành phố
            $table->unique(['city_id', 'name']);
            $table->unique(['city_id', 'slug']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('districts');
    }
};
