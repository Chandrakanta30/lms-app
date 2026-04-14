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
        $table->string('department')->nullable()->after('email');
        $table->string('designation')->nullable()->after('department');
        $table->string('qualification')->nullable()->after('designation');
        $table->integer('experience_years')->default(0)->after('qualification');
    });
}

public function down(): void
{
    Schema::table('users', function (Blueprint $table) {
        $table->dropColumn(['department', 'designation', 'qualification', 'experience_years']);
    });
}
};
