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
            $table->integer('current_gap')->comment('Khoảng cách hiện tại')->default(0);
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
