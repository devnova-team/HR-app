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
        Schema::create('attendance_records', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('employee_id')->constrained('employees')->cascadeOnDelete();
            $table->foreignUuid('work_schedule_id')->nullable()->constrained('work_schedules')->nullOnDelete();
            $table->unique(['employee_id', 'work_date']);
            $table->date('work_date');
            $table->timestamp('clock_in_time');
            $table->timestamp('clock_out_time')->nullable();
            $table->string('selfie_url');
            $table->decimal('clock_in_latitude');
            $table->decimal('clock_in_longitude');
            $table->decimal('clock_out_latitude')->nullable();
            $table->decimal('clock_out_longitude')->nullable();
            $table->text('notes')->nullable();
            $table->string('status');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('work_attendance_records');
    }
};
