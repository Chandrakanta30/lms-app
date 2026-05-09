<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('training_sessions', function (Blueprint $table) {
            if (!Schema::hasColumn('training_sessions', 'session_brief_type')) {
                $table->string('session_brief_type')->nullable()->after('topic');
            }

            if (!Schema::hasColumn('training_sessions', 'session_comments')) {
                $table->text('session_comments')->nullable()->after('session_brief_type');
            }

            if (!Schema::hasColumn('training_sessions', 'start_time')) {
                $table->time('start_time')->nullable()->after('session_comments');
            }

            if (!Schema::hasColumn('training_sessions', 'end_time')) {
                $table->time('end_time')->nullable()->after('start_time');
            }
        });
    }

    public function down(): void
    {
        Schema::table('training_sessions', function (Blueprint $table) {
            $columns = collect(['session_brief_type', 'session_comments', 'start_time', 'end_time'])
                ->filter(fn ($column) => Schema::hasColumn('training_sessions', $column))
                ->all();

            if (!empty($columns)) {
                $table->dropColumn($columns);
            }
        });
    }
};
