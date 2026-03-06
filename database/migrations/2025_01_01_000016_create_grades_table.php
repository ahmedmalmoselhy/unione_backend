<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('grades', function (Blueprint $table) {
            $table->id();
            $table->foreignId('enrollment_id')->unique()->constrained('enrollments')->cascadeOnDelete();
            $table->decimal('midterm', 5, 2)->nullable();
            $table->decimal('final', 5, 2)->nullable();
            // Covers labs, assignments, quizzes, etc.
            $table->decimal('coursework', 5, 2)->nullable();
            // Can be computed (midterm + final + coursework) or stored for override
            $table->decimal('total', 5, 2)->nullable();
            // e.g. A, B+, C
            $table->string('letter_grade', 3)->nullable();
            // e.g. 4.0, 3.7, 3.0 — used for GPA computation
            $table->decimal('grade_points', 3, 2)->nullable();
            $table->foreignId('graded_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('graded_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('grades');
    }
};
