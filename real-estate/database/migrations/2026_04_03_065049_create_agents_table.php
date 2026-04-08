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
        Schema::create('agents', function (Blueprint $table) {
            $table->id()->comment('ID định danh duy nhất cho mỗi đại lý');
            $table->foreignId('user_id')->unique()->constrained('users')->onDelete('cascade')->comment('Khóa ngoại liên kết đến người dùng (bảng users)');
            $table->string('agency_name')->nullable()->comment('Tên công ty hoặc đại lý bất động sản');
            $table->string('license_number')->unique()->nullable()->comment('Số giấy phép hành nghề duy nhất của đại lý');
            $table->text('bio')->nullable()->comment('Tiểu sử hoặc mô tả về đại lý');
            $table->string('website')->nullable()->comment('Địa chỉ trang web của đại lý hoặc công ty');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('agents');
    }
};
