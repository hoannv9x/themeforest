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
        Schema::create('topic_vocabulary', function (Blueprint $table) {
            $table->unsignedBigInteger('topic_id');
            $table->unsignedBigInteger('vocabulary_id');

            // Set composite primary key
            $table->primary(['topic_id', 'vocabulary_id']);

            // Add foreign key constraints
            $table->foreign('topic_id')
                ->references('id')
                ->on('vocabulary_topics')
                ->onDelete('cascade');

            $table->foreign('vocabulary_id')
                ->references('id')
                ->on('vocabularies')
                ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('topic_vocabulary');
    }
};
