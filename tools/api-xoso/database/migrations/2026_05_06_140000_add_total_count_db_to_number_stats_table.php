<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('number_stats', function (Blueprint $table) {
            if (!Schema::hasColumn('number_stats', 'total_count_db')) {
                $table->bigInteger('total_count_db')->default(0)->after('total_count');
            }
        });
    }

    public function down(): void
    {
        Schema::table('number_stats', function (Blueprint $table) {
            if (Schema::hasColumn('number_stats', 'total_count_db')) {
                $table->dropColumn('total_count_db');
            }
        });
    }
};

