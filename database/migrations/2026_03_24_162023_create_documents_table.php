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
        Schema::create('master_documents', function (Blueprint $table) {
            $table->id();
            $table->string('doc_name');
            $table->string('doc_number')->unique();
            $table->string('version');
            $table->string('file_path');
            $table->enum('doc_type', ['SOP', 'Protocol', 'PPT', 'Others']);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('master_documents');
    }
};
