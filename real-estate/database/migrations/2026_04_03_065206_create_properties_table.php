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
        Schema::create('properties', function (Blueprint $table) {
            $table->id()->comment('ID định danh duy nhất cho mỗi bất động sản');
            $table->foreignId('user_id')->nullable()->constrained('users')->onDelete('set null')->comment('Người dùng đã đăng tin (có thể là chủ sở hữu hoặc quản trị viên)');
            $table->foreignId('agent_id')->nullable()->constrained('agents')->onDelete('set null')->comment('Đại lý phụ trách bất động sản này');
            $table->foreignId('property_type_id')->constrained('property_types')->onDelete('restrict')->comment('Khóa ngoại liên kết đến loại bất động sản');
            $table->foreignId('city_id')->constrained('cities')->onDelete('restrict')->comment('Khóa ngoại liên kết đến thành phố');
            $table->foreignId('district_id')->nullable()->constrained('districts')->onDelete('set null')->comment('Khóa ngoại liên kết đến quận/huyện');
            $table->string('title')->comment('Tiêu đề của tin đăng bất động sản');
            $table->string('slug')->unique()->comment('Chuỗi định danh duy nhất cho URL');
            $table->longText('description')->comment('Mô tả chi tiết về bất động sản');
            $table->decimal('price', 15, 2)->comment('Giá của bất động sản');
            $table->string('currency', 3)->default('USD')->comment('Đơn vị tiền tệ (ví dụ: USD, VND)');
            $table->string('address')->comment('Địa chỉ chi tiết của bất động sản');
            $table->decimal('latitude', 10, 7)->nullable()->comment('Vĩ độ trên bản đồ');
            $table->decimal('longitude', 10, 7)->nullable()->comment('Kinh độ trên bản đồ');
            $table->unsignedTinyInteger('bedrooms')->nullable()->comment('Số lượng phòng ngủ');
            $table->unsignedTinyInteger('bathrooms')->nullable()->comment('Số lượng phòng tắm');
            $table->unsignedInteger('area_sqft')->nullable()->comment('Diện tích sử dụng (đơn vị: mét vuông hoặc feet vuông)');
            $table->unsignedInteger('lot_size_sqft')->nullable()->comment('Tổng diện tích lô đất (đơn vị: mét vuông hoặc feet vuông)');
            $table->unsignedSmallInteger('year_built')->nullable()->comment('Năm xây dựng');
            $table->enum('status', ['pending', 'active', 'sold', 'rented', 'inactive'])->default('pending')->comment('Trạng thái của bất động sản (chờ duyệt, đang bán, đã bán, đã cho thuê, không hoạt động)');
            $table->boolean('is_featured')->default(false)->comment('Cờ xác định bất động sản có phải là nổi bật hay không');
            $table->unsignedInteger('views_count')->default(0)->comment('Số lượt xem tin đăng');
            $table->timestamp('published_at')->nullable()->comment('Thời điểm tin đăng được công khai');
            $table->timestamps();

            $table->index('price');
            $table->index('status');
            $table->index('is_featured');
            $table->index(['latitude', 'longitude']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('properties');
    }
};
