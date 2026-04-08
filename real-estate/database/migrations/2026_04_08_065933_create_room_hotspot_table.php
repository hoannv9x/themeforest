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
        Schema::create('room_hotspots', function (Blueprint $table) {
            $table->id()->comment('ID định danh duy nhất cho mỗi điểm nóng (hotspot)');
            $table->foreignId('from_room_id')->constrained('rooms')->onDelete('cascade')->comment('ID của phòng chứa điểm nóng này (phòng hiện tại)');
            $table->string('to_room_slug')->comment('Slug của phòng sẽ được chuyển đến khi nhấp vào điểm nóng');
            $table->decimal('pitch', 10, 7)->comment('Tọa độ dọc (lên/xuống) của điểm nóng trong ảnh panorama');
            $table->decimal('yaw', 10, 7)->comment('Tọa độ ngang (trái/phải) của điểm nóng trong ảnh panorama');
            $table->string('text', 255)->nullable()->comment('Văn bản hiển thị khi di chuột qua điểm nóng');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('room_hotspots');
    }
};
