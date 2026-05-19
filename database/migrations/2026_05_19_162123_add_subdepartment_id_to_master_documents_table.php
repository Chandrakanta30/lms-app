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
        Schema::table('master_documents', function (Blueprint $table) {
            $table->unsignedBigInteger('subdepartment_id')->nullable()->after('department_id');
        });
    }

    public function down()
    {
        Schema::table('master_documents', function (Blueprint $table) {
            $table->dropColumn('subdepartment_id');
        });
    }
};
