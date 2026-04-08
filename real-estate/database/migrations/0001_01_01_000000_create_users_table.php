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
        Schema::create('users', function (Blueprint $table) {
            $table->id()->comment('ID định danh duy nhất cho mỗi người dùng');
            $table->string('name')->comment('Tên đầy đủ của người dùng');
            $table->string('email')->unique()->comment('Địa chỉ email duy nhất của người dùng, dùng để đăng nhập');
            $table->timestamp('email_verified_at')->nullable()->comment('Thời điểm email được xác thực');
            $table->string('password')->comment('Mật khẩu đã được mã hóa của người dùng');
            $table->boolean('is_admin')->default(false)->comment('Cờ xác định người dùng có phải là quản trị viên hay không (true/false)');
            $table->boolean('is_active')->default(true)->comment('Cờ xác định tài khoản có đang hoạt động hay không (true/false)');
            $table->string('profile_picture')->nullable()->comment('Đường dẫn đến ảnh đại diện của người dùng');
            $table->string('phone_number')->nullable()->comment('Số điện thoại của người dùng');
            $table->string('address')->nullable()->comment('Địa chỉ của người dùng');
            $table->rememberToken()->comment('Token để "ghi nhớ" người dùng cho các phiên đăng nhập sau');
            $table->timestamps();
        });

        Schema::create('password_reset_tokens', function (Blueprint $table) {
            $table->string('email')->primary();
            $table->string('token');
            $table->timestamp('created_at')->nullable();
        });

        Schema::create('sessions', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->foreignId('user_id')->nullable()->index();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->longText('payload');
            $table->integer('last_activity')->index();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('users');
        Schema::dropIfExists('password_reset_tokens');
        Schema::dropIfExists('sessions');
    }
};
