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
        Schema::create('rooms', function (Blueprint $table) {
            $table->id()->comment('ID định danh duy nhất cho mỗi phòng (dùng cho tour 360)');
            $table->foreignId('property_id')->constrained('properties')->onDelete('cascade')->comment('Khóa ngoại liên kết đến bất động sản');
            $table->string('name', 255)->comment('Tên của phòng (ví dụ: Phòng khách, Bếp)');
            $table->string('slug', 255)->unique()->comment('Chuỗi định danh duy nhất cho phòng, dùng làm sceneId trong Pannellum');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('rooms');
    }
};
