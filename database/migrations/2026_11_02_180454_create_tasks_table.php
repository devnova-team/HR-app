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
        Schema::create('tasks', function (Blueprint $table) {
            $table->uuid('id')->primary();

            $table->foreignUuid('assigned_by_id')
                ->constrained('employees');

            $table->foreignUuid('sprint_id')
                ->nullable()
                ->constrained('sprints');

            $table->string('title');
            $table->text('description');

            $table->string('status')->default('toDo');
            $table->string('priority');
            $table->string('difficulty');

            $table->integer('progress')->default(0);
            $table->integer('story_points')->nullable();

            $table->timestamp('due_date');
            $table->timestamp('completed_at')->nullable();

            $table->text('completion_summary')->nullable();
            $table->text('completion_notes')->nullable();

            $table->timestamps();

            $table->index(['assigned_by_id', 'status', 'due_date']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tasks');
    }
};
