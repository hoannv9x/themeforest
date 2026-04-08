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
        Schema::create('property_types', function (Blueprint $table) {
            $table->id()->comment('ID định danh duy nhất cho mỗi loại bất động sản');
            $table->string('name')->unique()->comment('Tên của loại bất động sản (ví dụ: Căn hộ, Nhà riêng)');
            $table->string('slug')->unique()->comment('Chuỗi định danh duy nhất cho URL (ví dụ: can-ho, nha-rieng)');
            $table->text('description')->nullable()->comment('Mô tả chi tiết về loại bất động sản');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('property_types');
    }
};
