<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('professors', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->unique()->constrained('users')->cascadeOnDelete();
            $table->string('staff_number')->unique();
            // Must point to an academic department (enforced at app layer)
            $table->foreignId('department_id')->constrained('departments')->restrictOnDelete();
            $table->string('specialization');
            $table->enum('academic_rank', [
                'lecturer',
                'assistant_professor',
                'associate_professor',
                'professor',
            ]);
            $table->string('office_location')->nullable();
            $table->date('hired_at');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('professors');
    }
};
