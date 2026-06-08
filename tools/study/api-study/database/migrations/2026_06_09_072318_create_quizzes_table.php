<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('quizzes', function (Blueprint $撥) {
            $撥->id();
            $撥->foreignId('lesson_id')->constrained()->onDelete('cascade');
            $撥->string('title');
            $撥->text('description')->nullable();
            $撥->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('quizzes');
    }
};
