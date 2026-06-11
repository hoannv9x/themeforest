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
        Schema::create('vocabularies', function (Blueprint $table) {
            $table->id();
            $table->string('word')->index();
            $table->string('ipa')->nullable();
            $table->string('pronunciation')->nullable();
            $table->text('meaning');
            $table->enum('part_of_speech', [
                'noun',
                'verb',
                'adjective',
                'adverb',
                'pronoun',
                'preposition',
                'conjunction',
                'interjection',
                'phrase',
                'idiom'
            ]);
            $table->enum('difficulty', [
                'beginner',
                'elementary',
                'intermediate',
                'upper_intermediate',
                'advanced'
            ])->default('beginner');
            $table->string('image_url')->nullable();
            $table->string('audio_url')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index('word', 'idx_word');
            $table->index('difficulty', 'idx_difficulty');
            $table->index('part_of_speech', 'idx_part_of_speech');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('vocabularies');
    }
};
