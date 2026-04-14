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
        Schema::create('number_stats', function (Blueprint $table) {
            $table->id();
            $table->char('number', 10);
            $table->string('total_count')->comment('Số lần xuất hiện')->default(0);
            $table->date('last_appeared_at')->comment('Ngày cuối cùng xuất hiện')->nullable();
            $table->integer('current_gap')->comment('Khoảng cách hiện tại')->default(0);
            $table->boolean('never_hit')->default(false);
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
