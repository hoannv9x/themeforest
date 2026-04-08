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
        Schema::create('cities', function (Blueprint $table) {
            $table->id()->comment('ID định danh duy nhất cho mỗi thành phố');
            $table->string('name')->unique()->comment('Tên của thành phố');
            $table->string('slug')->unique()->comment('Chuỗi định danh duy nhất cho URL');
            $table->string('state')->nullable()->comment('Bang hoặc tỉnh (nếu có)');
            $table->string('country')->nullable()->comment('Quốc gia');
            $table->decimal('latitude', 10, 7)->nullable()->comment('Vĩ độ của thành phố');
            $table->decimal('longitude', 10, 7)->nullable()->comment('Kinh độ của thành phố');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cities');
    }
};
