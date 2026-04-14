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
            $table->string('correct_answer', 255)->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('master_questions', function (Blueprint $table) {
            $table->enum('correct_answer', ['Yes', 'No'])->change();
        });
    }
};
