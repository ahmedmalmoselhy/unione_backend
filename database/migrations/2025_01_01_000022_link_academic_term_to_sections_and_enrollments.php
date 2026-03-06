<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Replace loose academic_year + semester columns on sections with a proper FK
        Schema::table('sections', function (Blueprint $table) {
            $table->foreignId('academic_term_id')
                  ->nullable()
                  ->after('professor_id')
                  ->constrained('academic_terms')
                  ->restrictOnDelete();

            // Drop the old loose columns — now owned by academic_terms
            $table->dropIndex(['course_id', 'academic_year', 'semester']);
            $table->dropColumn(['academic_year', 'semester']);

            $table->index(['course_id', 'academic_term_id']);
        });

        // Link enrollments to the term directly for easier queries
        Schema::table('enrollments', function (Blueprint $table) {
            $table->foreignId('academic_term_id')
                  ->nullable()
                  ->after('section_id')
                  ->constrained('academic_terms')
                  ->restrictOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('enrollments', function (Blueprint $table) {
            $table->dropForeign(['academic_term_id']);
            $table->dropColumn('academic_term_id');
        });

        Schema::table('sections', function (Blueprint $table) {
            $table->dropForeign(['academic_term_id']);
            $table->dropIndex(['course_id', 'academic_term_id']);
            $table->dropColumn('academic_term_id');

            // Restore original columns
            $table->smallInteger('academic_year')->unsigned()->after('professor_id');
            $table->enum('semester', ['first', 'second', 'summer'])->after('academic_year');
            $table->index(['course_id', 'academic_year', 'semester']);
        });
    }
};
