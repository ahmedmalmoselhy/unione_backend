<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('student_department_history', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained('students')->cascadeOnDelete();
            // Null on the first assignment (no previous department)
            $table->foreignId('from_department_id')->nullable()->constrained('departments')->nullOnDelete();
            $table->foreignId('to_department_id')->constrained('departments')->restrictOnDelete();
            $table->timestamp('switched_at')->useCurrent();
            // The admin/staff member who processed the switch
            $table->foreignId('switched_by')->constrained('users')->restrictOnDelete();
            $table->text('note')->nullable();

            $table->index('student_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('student_department_history');
    }
};
