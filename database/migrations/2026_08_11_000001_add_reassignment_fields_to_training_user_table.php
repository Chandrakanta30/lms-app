<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('training_user', function (Blueprint $table) {
            $table->timestamp('reassigned_at')->nullable()->after('attendance_marked_by');
            $table->string('reassignment_mode', 20)->nullable()->after('reassigned_at');
            $table->text('reassignment_note')->nullable()->after('reassignment_mode');
            $table->foreignId('reassigned_from_training_id')
                ->nullable()
                ->after('reassignment_note')
                ->constrained('training_modules')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('training_user', function (Blueprint $table) {
            $table->dropConstrainedForeignId('reassigned_from_training_id');
            $table->dropColumn(['reassignment_note', 'reassignment_mode', 'reassigned_at']);
        });
    }
};
