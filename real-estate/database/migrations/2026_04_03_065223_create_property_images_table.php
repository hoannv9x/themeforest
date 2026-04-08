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
        Schema::create('property_images', function (Blueprint $table) {
            $table->id()->comment('ID định danh duy nhất cho mỗi ảnh');
            $table->foreignId('property_id')->constrained('properties')->onDelete('cascade')->comment('Khóa ngoại liên kết đến bất động sản');
            $table->string('image_path')->comment('Đường dẫn đến tệp ảnh');
            $table->string('image_host')->nullable()->comment('Host lưu trữ ảnh (nếu dùng dịch vụ bên thứ ba như S3)');
            $table->string('caption')->nullable()->comment('Chú thích cho ảnh');
            $table->unsignedTinyInteger('order')->default(0)->comment('Thứ tự hiển thị của ảnh');
            $table->timestamps();

            $table->index(['property_id', 'order']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('property_images');
    }
};
