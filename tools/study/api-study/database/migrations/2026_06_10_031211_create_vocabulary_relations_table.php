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
        Schema::create('vocabulary_relations', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('vocabulary_id');
            $table->unsignedBigInteger('related_vocabulary_id');
            $table->enum('relation_type', ['synonym', 'antonym']);
            $table->timestamps();

            $table->foreign('vocabulary_id')
                ->references('id')
                ->on('vocabularies')
                ->onDelete('cascade');

            $table->foreign('related_vocabulary_id')
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
        Schema::dropIfExists('vocabulary_relations');
    }
};
