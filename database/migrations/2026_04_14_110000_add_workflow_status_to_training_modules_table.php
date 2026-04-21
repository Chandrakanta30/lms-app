<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('training_modules', function (Blueprint $table) {
            $table->string('status')->default('created')->after('training_type');
        });
    }

    public function down(): void
    {
        Schema::table('training_modules', function (Blueprint $table) {
            $table->dropColumn('status');
        });
    }
};
