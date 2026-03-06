<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('academic_terms', function (Blueprint $table) {
            $table->id();
            $table->string('name');                          // e.g. "First Semester 2025/2026"
            $table->string('name_ar');                       // e.g. "الفصل الأول 2025/2026"
            $table->smallInteger('academic_year');           // e.g. 2025 (start year)
            $table->enum('semester', ['first', 'second', 'summer']);
            $table->date('starts_at');                       // Semester start date
            $table->date('ends_at');                         // Semester end date
            $table->date('registration_starts_at');          // When students can register
            $table->date('registration_ends_at');            // Registration deadline
            $table->date('withdrawal_deadline')->nullable(); // Last day to drop without penalty
            $table->date('exam_starts_at')->nullable();      // Final exam period start
            $table->date('exam_ends_at')->nullable();        // Final exam period end
            $table->date('grade_submission_deadline')->nullable(); // Professor grade entry deadline
            $table->boolean('is_active')->default(false);    // Only one term active at a time
            $table->timestamps();

            $table->unique(['academic_year', 'semester']);
            $table->index('is_active');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('academic_terms');
    }
};
