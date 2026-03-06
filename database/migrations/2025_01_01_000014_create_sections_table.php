<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sections', function (Blueprint $table) {
            $table->id();
            $table->foreignId('course_id')->constrained('courses')->restrictOnDelete();
            $table->foreignId('professor_id')->constrained('professors')->restrictOnDelete();
            $table->smallInteger('academic_year')->unsigned();
            $table->enum('semester', ['first', 'second', 'summer']);
            $table->smallInteger('capacity')->unsigned();
            $table->string('room')->nullable();
            // JSON: [{day, start_time, end_time, type: 'lecture'|'lab'}]
            $table->json('schedule')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index(['course_id', 'academic_year', 'semester']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sections');
    }
};
