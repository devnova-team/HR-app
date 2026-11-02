<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {

        Schema::table('levels', function (Blueprint $table) {
            $table->unique('name'); // منع تكرار اسم المستوى
            $table->unique('rank'); // منع تكرار رقم الرتبة
        });


        Schema::table('departments', function (Blueprint $table) {
            $table->unique('name');
        });


        Schema::table('job_titles', function (Blueprint $table) {
            $table->unique(['department_id', 'name']);
        });

        // 4. تعديلات جدول Countries
        Schema::table('countries', function (Blueprint $table) {
            $table->unique('name');
        });

        // 5. تعديلات جدول Governorates
        Schema::table('governorates', function (Blueprint $table) {
            $table->unique(['country_id', 'name']);
        });


        Schema::table('cities', function (Blueprint $table) {
            $table->unique(['governorate_id', 'name']);
        });
    }

    public function down(): void
    {
        Schema::table('levels', function (Blueprint $table) {
            $table->dropUnique(['name']);
            $table->dropUnique(['rank']);
        });

        Schema::table('departments', function (Blueprint $table) {
            $table->dropUnique(['name']);
        });

        Schema::table('job_titles', function (Blueprint $table) {
            $table->dropUnique(['department_id', 'name']);
        });

        Schema::table('countries', function (Blueprint $table) {
            $table->dropUnique(['name']);
        });

        Schema::table('governorates', function (Blueprint $table) {
            $table->dropUnique(['country_id', 'name']);
        });

        Schema::table('cities', function (Blueprint $table) {
            $table->dropUnique(['governorate_id', 'name']);
        });
    }
};
