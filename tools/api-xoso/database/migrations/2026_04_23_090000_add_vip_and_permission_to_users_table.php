<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (!Schema::hasColumn('users', 'permission')) {
                $table->enum('permission', ['user', 'developer'])->default('user')->after('role');
            }

            if (!Schema::hasColumn('users', 'vip_expired_at')) {
                $table->timestamp('vip_expired_at')->nullable()->after('permission');
            }
        });

        if (DB::getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE users MODIFY role ENUM('user','admin','vip') NOT NULL DEFAULT 'user'");
        }
    }

    public function down(): void
    {
        if (DB::getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE users MODIFY role ENUM('user','admin') NOT NULL DEFAULT 'user'");
        }

        Schema::table('users', function (Blueprint $table) {
            if (Schema::hasColumn('users', 'vip_expired_at')) {
                $table->dropColumn('vip_expired_at');
            }
            if (Schema::hasColumn('users', 'permission')) {
                $table->dropColumn('permission');
            }
        });
    }
};
