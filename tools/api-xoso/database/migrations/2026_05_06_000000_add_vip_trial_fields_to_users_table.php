<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (!Schema::hasColumn('users', 'vip_trial_used')) {
                $table->boolean('vip_trial_used')->default(false)->after('vip_expired_at');
            }

            if (!Schema::hasColumn('users', 'vip_trial_started_at')) {
                $table->timestamp('vip_trial_started_at')->nullable()->after('vip_trial_used');
            }
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (Schema::hasColumn('users', 'vip_trial_used')) {
                $table->dropColumn('vip_trial_used');
            }
            if (Schema::hasColumn('users', 'vip_trial_started_at')) {
                $table->dropColumn('vip_trial_started_at');
            }
        });
    }
};
