<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('department_course', function (Blueprint $table) {
            $table->id();
            $table->foreignId('department_id')->constrained('departments')->cascadeOnDelete();
            $table->foreignId('course_id')->constrained('courses')->cascadeOnDelete();
            // True = this department originally owns the course; false = shared from another dept
            $table->boolean('is_owner')->default(false);

            $table->unique(['department_id', 'course_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('department_course');
    }
};
