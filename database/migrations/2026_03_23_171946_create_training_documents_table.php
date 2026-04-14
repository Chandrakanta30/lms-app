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
        Schema::create('training_documents', function (Blueprint $table) {
            $table->id();
            $table->foreignId('training_id')->constrained('training_modules')->onDelete('cascade');
            $table->string('doc_type'); // e.g., SOP, Manual, Video
            $table->string('doc_name');
            $table->string('doc_number');
            $table->string('doc_version');
            $table->string('file_path');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('training_documents');
    }
};
