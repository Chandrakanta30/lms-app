<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('master_documents', function (Blueprint $table) {

            $table->unsignedBigInteger('department_id')->nullable();

            $table->unsignedBigInteger('section_id')->nullable();

            $table->foreign('department_id')
                  ->references('id')
                  ->on('departments')
                  ->onDelete('set null');

            $table->foreign('section_id')
                  ->references('id')
                  ->on('section_masters')
                  ->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::table('master_documents', function (Blueprint $table) {

            $table->dropForeign(['department_id']);
            $table->dropForeign(['section_id']);

            $table->dropColumn([
                'department_id',
                'section_id'
            ]);
        });
    }
};