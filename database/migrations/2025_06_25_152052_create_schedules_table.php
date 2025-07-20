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
        Schema::create('schedules', function (Blueprint $table) {
            $table->id();
            $table->year('year');
            $table->tinyInteger('month');
            $table->enum('status', ['active', 'inactive'])->default('active');
            $table->unsignedTinyInteger('max_shift_per_employee')->default(5); // Max shifts per employee for this schedule
            $table->json('selected_employees'); // array of employee IDs used in this schedule
            $table->json('result'); // Format: {"monday":{"morning":"Alice","night":"Bob"}, ...}
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('schedules');
    }
};
