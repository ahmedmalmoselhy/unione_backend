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
            $table->unsignedTinyInteger('credit_hours');
            $table->unsignedTinyInteger('lecture_hours');
            $table->unsignedTinyInteger('lab_hours')->default(0);
            // The academic year level this course is typically taken in (1–5)
            $table->unsignedTinyInteger('level');
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
