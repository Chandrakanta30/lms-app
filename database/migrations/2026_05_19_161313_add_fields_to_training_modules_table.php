<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::table('training_modules', function (Blueprint $table) {
            $table->unsignedBigInteger('annual_parent_id')->nullable()->after('parent_id');
            $table->enum('is_anuual', ['0', '1'])->default('0')->after('annual_parent_id');
            $table->enum('frequency', ['monthly', 'quarterly', 'half_yearly', 'yearly'])->nullable()->after('is_anuual');
       
        });
    }

    public function down()
    {
        Schema::table('training_modules', function (Blueprint $table) {
            $table->dropColumn(['is_anuual', 'frequency', 'annual_parent_id']);
        });
    }
};
