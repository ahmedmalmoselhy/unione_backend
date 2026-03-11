<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('student_term_gpas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained()->cascadeOnDelete();
            $table->foreignId('academic_term_id')->constrained()->cascadeOnDelete();
            $table->decimal('gpa', 4, 2)->nullable();
            $table->unsignedSmallInteger('credit_hours')->default(0);
            $table->timestamps();

            $table->unique(['student_id', 'academic_term_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('student_term_gpas');
    }
};
