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
        Schema::create('trainer_training', function (Blueprint $table) {
            $table->id();
            $table->foreignId('training_module_id')
            ->constrained('training_modules')
            ->onDelete('cascade');           
            $table->foreignId('user_id')->constrained()->onDelete('cascade'); // The trainer
            $table->date('start_date')->nullable();
            $table->date('end_date')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('trainer_training');
    }
};
