<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('training_sessions', function (Blueprint $table) {
            if (!Schema::hasColumn('training_sessions', 'training_module_id')) {
                $table->foreignId('training_module_id')
                    ->nullable()
                    ->after('trainee_id')
                    ->constrained('training_modules')
                    ->nullOnDelete();
            }
        });
    }

    public function down(): void
    {
        Schema::table('training_sessions', function (Blueprint $table) {
            if (Schema::hasColumn('training_sessions', 'training_module_id')) {
                $table->dropConstrainedForeignId('training_module_id');
            }
        });
    }
};
