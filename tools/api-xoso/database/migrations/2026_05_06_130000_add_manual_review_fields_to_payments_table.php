<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('payments', function (Blueprint $table) {
            $table->enum('manual_review_status', ['none', 'requested'])->default('none')->after('status');
            $table->timestamp('manual_review_requested_at')->nullable()->after('manual_review_status');
        });
    }

    public function down(): void
    {
        Schema::table('payments', function (Blueprint $table) {
            $table->dropColumn(['manual_review_status', 'manual_review_requested_at']);
        });
    }
};

