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
 
    // 1. The Question Paper (Linked to Training Module)
    Schema::create('questions', function (Blueprint $table) {
        $table->id();
        $table->foreignId('training_module_id')->constrained('training_modules')->onDelete('cascade');
        $table->text('question_text');
        $table->enum('correct_answer', ['Yes', 'No']);
        $table->timestamps();
    });

    // 2. The Student Results
    Schema::create('exam_results', function (Blueprint $table) {
        $table->id();
        $table->foreignId('user_id')->constrained();
        $table->foreignId('training_module_id')->constrained('training_modules');
        $table->integer('score'); // e.g., 8 out of 10
        $table->boolean('is_passed');
        $table->timestamps();
    });

    Schema::create('user_answers', function (Blueprint $table) {
        $table->id();
        $table->foreignId('exam_result_id')->constrained()->onDelete('cascade');
        $table->foreignId('question_id')->constrained();
        $table->enum('user_choice', ['Yes', 'No']);
        $table->boolean('is_correct');
    });

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('questions');
        Schema::dropIfExists('exam_results');
        Schema::dropIfExists('user_answers');


    }
};
