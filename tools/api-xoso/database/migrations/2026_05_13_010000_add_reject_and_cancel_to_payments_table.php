<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (DB::getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE payments MODIFY status ENUM('pending','paid','expired','failed','cancelled','rejected') NOT NULL DEFAULT 'pending'");
        }

        Schema::table('payments', function (Blueprint $table) {
            if (!Schema::hasColumn('payments', 'cancelled_at')) {
                $table->timestamp('cancelled_at')->nullable()->after('paid_at');
            }
            if (!Schema::hasColumn('payments', 'cancelled_reason')) {
                $table->string('cancelled_reason')->nullable()->after('cancelled_at');
            }

            if (!Schema::hasColumn('payments', 'rejected_at')) {
                $table->timestamp('rejected_at')->nullable()->after('cancelled_reason');
            }
            if (!Schema::hasColumn('payments', 'rejected_reason')) {
                $table->text('rejected_reason')->nullable()->after('rejected_at');
            }
            if (!Schema::hasColumn('payments', 'rejected_by_user_id')) {
                $table->foreignId('rejected_by_user_id')->nullable()->after('rejected_reason')->constrained('users')->nullOnDelete();
            }
        });
    }

    public function down(): void
    {
        Schema::table('payments', function (Blueprint $table) {
            if (Schema::hasColumn('payments', 'rejected_by_user_id')) {
                $table->dropConstrainedForeignId('rejected_by_user_id');
            }
            $table->dropColumn(['rejected_reason', 'rejected_at', 'cancelled_reason', 'cancelled_at']);
        });

        if (DB::getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE payments MODIFY status ENUM('pending','paid','expired','failed') NOT NULL DEFAULT 'pending'");
        }
    }
};

