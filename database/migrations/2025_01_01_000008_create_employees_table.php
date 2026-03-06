<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('employees', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->unique()->constrained('users')->cascadeOnDelete();
            $table->string('staff_number')->unique();
            // Must point to a managerial department (enforced at app layer)
            $table->foreignId('department_id')->constrained('departments')->restrictOnDelete();
            $table->string('job_title');
            $table->enum('employment_type', ['full_time', 'part_time', 'contract']);
            $table->decimal('salary', 10, 2)->nullable();
            $table->date('hired_at');
            $table->date('terminated_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('employees');
    }
};
