<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('students', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->unique()->constrained('users')->cascadeOnDelete();
            $table->string('student_number')->unique();
            $table->foreignId('faculty_id')->constrained('faculties')->restrictOnDelete();
            // Nullable: null for faculties with enrollment_type = 'none'
            $table->foreignId('department_id')->nullable()->constrained('departments')->nullOnDelete();
            $table->tinyInteger('academic_year')->unsigned()->default(1);
            $table->enum('semester', ['first', 'second', 'summer'])->default('first');
            $table->enum('enrollment_status', [
                'active',
                'suspended',
                'graduated',
                'withdrawn',
            ])->default('active');
            $table->decimal('gpa', 3, 2)->nullable();
            $table->date('enrolled_at');
            $table->date('graduated_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('students');
    }
};
