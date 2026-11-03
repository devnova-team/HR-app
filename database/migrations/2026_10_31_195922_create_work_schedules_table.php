<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
 
    public function up(): void
    {
        Schema::create('work_schedules', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('name');
            $table->time('clock_in_time');
            $table->time('clock_out_time');
            $table->integer('grace_period_minutes')->default(0);
            $table->boolean('is_default')->default(false);
            $table->timestamps();
        });
    }

  
    public function down(): void
    {
        Schema::dropIfExists('work_schedules');
    }
};
