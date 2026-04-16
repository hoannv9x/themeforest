<?php

use App\Models\Algorithm;
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
        Schema::create('prediction_details', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('prediction_id');
            $table->enum('algorithm', Algorithm::CODES);
            $table->char('number', 2);
            $table->float('score')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('prediction_details');
    }
};
