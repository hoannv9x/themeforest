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
        Schema::create('prediction_snapshots', function (Blueprint $table) {
            $table->id();
            $table->date('date')->index();
            $table->string('region', 20)->index();
            $table->boolean('is_vip')->default(false)->index();
            $table->longText('content')->comment('JSON blob of aggregated prediction data');
            $table->timestamps();

            $table->unique(['date', 'region', 'is_vip']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('prediction_snapshots');
    }
};
