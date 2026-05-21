<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('training_modules', function (Blueprint $table) {
            if (!Schema::hasColumn('training_modules', 'department_id')) {
                $table->unsignedBigInteger('department_id')->nullable()->after('frequency');
            }

            if (!Schema::hasColumn('training_modules', 'subdepartment_id')) {
                $table->unsignedBigInteger('subdepartment_id')->nullable()->after('department_id');
            }
        });
    }

    public function down(): void
    {
        Schema::table('training_modules', function (Blueprint $table) {
            if (Schema::hasColumn('training_modules', 'subdepartment_id')) {
                $table->dropColumn('subdepartment_id');
            }

            if (Schema::hasColumn('training_modules', 'department_id')) {
                $table->dropColumn('department_id');
            }
        });
    }
};

