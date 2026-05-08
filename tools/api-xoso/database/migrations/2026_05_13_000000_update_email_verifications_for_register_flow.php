<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('email_verifications', function (Blueprint $table) {
            $table->dropConstrainedForeignId('user_id');
            $table->dropColumn('used_at');
            $table->unique(['email']);
            $table->index(['email', 'expires_at']);
        });
    }

    public function down(): void
    {
        Schema::table('email_verifications', function (Blueprint $table) {
            if (Schema::hasColumn('email_verifications', 'email')) {
                $table->dropIndex(['email', 'expires_at']);
                $table->dropUnique(['email']);
            }

            if (!Schema::hasColumn('email_verifications', 'used_at')) {
                $table->timestamp('used_at')->nullable();
            }

            if (!Schema::hasColumn('email_verifications', 'user_id')) {
                $table->foreignId('user_id')->after('id')->constrained()->cascadeOnDelete();
                $table->index(['user_id', 'email', 'expires_at']);
            }
        });
    }
};

