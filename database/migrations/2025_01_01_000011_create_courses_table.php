<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('courses', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique();
            $table->string('name');
            $table->string('name_ar');
            $table->text('description')->nullable();
            $table->tinyInteger('credit_hours')->unsigned();
            $table->tinyInteger('lecture_hours')->unsigned();
            $table->tinyInteger('lab_hours')->unsigned()->default(0);
            // The academic year level this course is typically taken in (1–5)
            $table->tinyInteger('level')->unsigned();
            $table->boolean('is_elective')->default(false);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('courses');
    }
};
