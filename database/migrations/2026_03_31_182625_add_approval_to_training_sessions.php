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
        Schema::table('training_sessions', function (Blueprint $table) {
            $table->unsignedBigInteger('approved_by')->nullable()->after('trainer_id');
            $table->boolean('is_approved')->default(false);
            $table->timestamp('approved_at')->nullable();
        
            $table->foreign('approved_by')->references('id')->on('users')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('training_sessions', function (Blueprint $table) {
            // 1. Drop the foreign key constraint first
            // Laravel's convention is: table_column_foreign
            $table->dropForeign(['approved_by']);
    
            // 2. Drop the columns
            $table->dropColumn(['approved_by', 'is_approved', 'approved_at']);
        });
    }
};
