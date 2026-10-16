<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('user_trainings', function (Blueprint $table) {
            $table->string('department', 10)->nullable()->after('training_module_id');
            $table->unique(['user_id', 'training_module_id', 'department'], 'user_training_department_signature_unique');
        });
    }

    public function down(): void
    {
        Schema::table('user_trainings', function (Blueprint $table) {
            $table->dropUnique('user_training_department_signature_unique');
            $table->dropColumn('department');
        });
    }
};
