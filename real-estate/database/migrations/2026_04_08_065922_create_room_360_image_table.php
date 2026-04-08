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
        Schema::create('room_360_images', function (Blueprint $table) {
            $table->id()->comment('ID định danh duy nhất cho ảnh 360');
            $table->foreignId('room_id')->unique()->constrained('rooms')->onDelete('cascade')->comment('Khóa ngoại liên kết đến phòng. Mỗi phòng chỉ có một ảnh 360.');
            $table->string('panorama_url', 255)->comment('Đường dẫn URL đến ảnh panorama equirectangular');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('room_360_images');
    }
};
