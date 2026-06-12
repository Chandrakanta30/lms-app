<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasColumn('training_modules', 'subdepartment_id')) {
            return;
        }

        DB::statement('ALTER TABLE training_modules MODIFY subdepartment_id JSON NULL');

        DB::statement("
            UPDATE training_modules
            SET subdepartment_id = JSON_ARRAY(subdepartment_id)
            WHERE subdepartment_id IS NOT NULL
              AND JSON_TYPE(subdepartment_id) <> 'ARRAY'
        ");
    }

    public function down(): void
    {
        if (!Schema::hasColumn('training_modules', 'subdepartment_id')) {
            return;
        }

        DB::statement("
            UPDATE training_modules
            SET subdepartment_id = JSON_UNQUOTE(JSON_EXTRACT(subdepartment_id, '$[0]'))
            WHERE subdepartment_id IS NOT NULL
        ");

        DB::statement('ALTER TABLE training_modules MODIFY subdepartment_id BIGINT UNSIGNED NULL');
    }
};
