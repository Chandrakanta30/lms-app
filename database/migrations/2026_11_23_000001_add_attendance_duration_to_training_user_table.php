<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('training_user', function (Blueprint $table) {
            $table->unsignedInteger('attendance_duration')->nullable()->after('attendance_marked_by');
        });
    }

    public function down(): void
    {
        Schema::table('training_user', function (Blueprint $table) {
            $table->dropColumn('attendance_duration');
        });
    }
};
