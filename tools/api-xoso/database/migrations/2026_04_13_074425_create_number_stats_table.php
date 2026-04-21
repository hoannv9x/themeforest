<?php

use App\Models\Number;
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
        Schema::create('number_stats', function (Blueprint $table) {
            $table->id();
            $table->char('number', 10);
            $table->bigInteger('total_count')->comment('Số lần xuất hiện')->default(0);
            $table->bigInteger('total_count_7_days')->comment('Số lần xuất hiện trong 7 ngày')->default(0);
            $table->bigInteger('total_count_30_days')->comment('Số lần xuất hiện trong 30 ngày')->default(0);
            $table->bigInteger('total_count_90_days')->comment('Số lần xuất hiện trong 90 ngày')->default(0);
            $table->bigInteger('total_count_180_days')->comment('Số lần xuất hiện trong 180 ngày')->default(0);
            $table->bigInteger('total_count_365_days')->comment('Số lần xuất hiện trong 365 ngày')->default(0);
            $table->date('last_appeared_at')->comment('Ngày cuối cùng xuất hiện')->nullable();
            $table->string('region')->comment('Vị trí')->default(Number::REGION_MB);
            $table->date('last_appeared_at_db')->comment('Ngày cuối cùng xuất hiện')->nullable();
            $table->integer('current_gap')->comment('Khoảng cách hiện tại')->default(0);
            $table->integer('max_gap')->comment('Khoảng cách tối đa')->default(0);
            $table->integer('current_gap_db')->comment('Khoảng cách hiện tại giải đặc biệt')->default(0);
            $table->integer('max_gap_db')->comment('Khoảng cách tối đa giải đặc biệt')->default(0);
            $table->integer('avg_gap')->comment('Khoảng cách bình bình')->default(0);
            $table->integer('avg_gap_db')->comment('Khoảng cách bình bình giải đặc biệt')->default(0);
            $table->integer('std_gap')->comment('Khoảng cách chuẩn')->default(0);
            $table->integer('std_gap_db')->comment('Khoảng cách chuẩn giải đặc biệt')->default(0);
            $table->timestamp('updated_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('number_stats');
    }
};
