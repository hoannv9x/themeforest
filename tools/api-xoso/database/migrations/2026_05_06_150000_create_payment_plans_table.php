<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payment_plans', function (Blueprint $table) {
            $table->id();
            $table->enum('type', ['vip', 'api']);
            $table->string('plan_key')->unique();
            $table->string('name');
            $table->unsignedInteger('duration_days');
            $table->unsignedBigInteger('amount');
            $table->unsignedInteger('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        $now = now();
        $rows = [
            ['type' => 'vip', 'plan_key' => 'vip_7d', 'name' => 'VIP 7 ngày', 'duration_days' => 7, 'amount' => 9900, 'sort_order' => 10],
            ['type' => 'vip', 'plan_key' => 'vip_15d', 'name' => 'VIP 15 ngày', 'duration_days' => 15, 'amount' => 17900, 'sort_order' => 20],
            ['type' => 'vip', 'plan_key' => 'vip_30d', 'name' => 'VIP 30 ngày', 'duration_days' => 30, 'amount' => 29900, 'sort_order' => 30],
            ['type' => 'vip', 'plan_key' => 'vip_6m', 'name' => 'VIP 6 tháng', 'duration_days' => 180, 'amount' => 149000, 'sort_order' => 40],
            ['type' => 'vip', 'plan_key' => 'vip_1y', 'name' => 'VIP 1 năm', 'duration_days' => 365, 'amount' => 259000, 'sort_order' => 50],
            ['type' => 'api', 'plan_key' => 'api_7d', 'name' => 'API 7 ngày', 'duration_days' => 7, 'amount' => 19900, 'sort_order' => 10],
            ['type' => 'api', 'plan_key' => 'api_15d', 'name' => 'API 15 ngày', 'duration_days' => 15, 'amount' => 34900, 'sort_order' => 20],
            ['type' => 'api', 'plan_key' => 'api_30d', 'name' => 'API 30 ngày', 'duration_days' => 30, 'amount' => 59900, 'sort_order' => 30],
            ['type' => 'api', 'plan_key' => 'api_6m', 'name' => 'API 6 tháng', 'duration_days' => 180, 'amount' => 299000, 'sort_order' => 40],
            ['type' => 'api', 'plan_key' => 'api_1y', 'name' => 'API 1 năm', 'duration_days' => 365, 'amount' => 499000, 'sort_order' => 50],
        ];

        foreach ($rows as &$row) {
            $row['created_at'] = $now;
            $row['updated_at'] = $now;
        }

        DB::table('payment_plans')->insert($rows);
    }

    public function down(): void
    {
        Schema::dropIfExists('payment_plans');
    }
};

