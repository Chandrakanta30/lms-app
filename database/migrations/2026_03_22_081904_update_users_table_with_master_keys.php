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
        Schema::table('users', function (Blueprint $table) {
            // Remove old string columns if they exist
            $table->dropColumn(['department', 'designation']);
    
            // Add foreign key columns
            $table->foreignId('department_id')->nullable()->constrained('departments')->onDelete('set null');
            $table->foreignId('designation_id')->nullable()->constrained('designations')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['department_id']);
            $table->dropForeign(['designation_id']);
            $table->dropColumn(['department_id', 'designation_id']);
            
            // Bring back the old strings if rolled back
            $table->string('department')->nullable();
            $table->string('designation')->nullable();
        });
    }
};
