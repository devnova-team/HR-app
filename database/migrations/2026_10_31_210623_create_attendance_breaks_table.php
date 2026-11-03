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
        Schema::create('attendance_breaks', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('attendance_record_id')->constrained('attendance_record')->cascadeOnDelete();
            $table->timestamps('started_at');
            $table->timestamps('ended_at')->nullable();
            $table->timestamps('created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('work_attendance_breaks');
    }
};
