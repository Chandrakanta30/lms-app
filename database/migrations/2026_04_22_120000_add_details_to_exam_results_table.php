<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('exam_results', function (Blueprint $table) {
            if (!Schema::hasColumn('exam_results', 'details')) {
                $table->json('details')->nullable()->after('is_passed');
            }
        });
    }

    public function down(): void
    {
        Schema::table('exam_results', function (Blueprint $table) {
            if (Schema::hasColumn('exam_results', 'details')) {
                $table->dropColumn('details');
            }
        });
    }
};
