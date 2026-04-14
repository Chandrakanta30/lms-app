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
        Schema::table('master_questions', function (Blueprint $table) {
            // Default to yes_no so existing data doesn't break
            $table->string('question_type')->default('yes_no')->after('question_text');
            // JSON column to store MCQ choices
            $table->json('options')->nullable()->after('question_type');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('master_questions', function (Blueprint $table) {
            $table->dropColumn(['question_type', 'options']);
        });
    }
};
