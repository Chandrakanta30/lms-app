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
        // 1. Drop existing tables to start fresh
        Schema::dropIfExists('user_answers');
        Schema::dropIfExists('exam_results');
        Schema::dropIfExists('questions');
        // Note: Keep training_modules but we will link them differently

        // 3. Create Questions linked to Master Documents (The Pool)
        Schema::create('master_questions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('master_document_id')->constrained('master_documents')->onDelete('cascade');
            $table->text('question_text');
            $table->enum('correct_answer', ['Yes', 'No']);
            $table->timestamps();
        });

        // 4. Pivot Table: Link Modules to Documents with "Random Quota"
        Schema::create('module_document_pivot', function (Blueprint $table) {
            $table->id();
            $table->foreignId('training_module_id')->constrained('training_modules')->onDelete('cascade');
            $table->foreignId('master_document_id')->constrained('master_documents')->onDelete('cascade');
            $table->integer('question_quota')->default(0); // How many random questions to pull
            $table->timestamps();
        });

        // 5. Re-create Exam Results (Logs)
        Schema::create('exam_results', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('training_module_id')->constrained('training_modules');
            $table->integer('total_questions_attempted');
            $table->integer('correct_answers');
            $table->decimal('percentage', 5, 2);
            $table->boolean('is_passed');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('exam_results');
        Schema::dropIfExists('module_document_pivot');
        Schema::dropIfExists('master_questions');
        Schema::dropIfExists('master_documents');
    }
};
