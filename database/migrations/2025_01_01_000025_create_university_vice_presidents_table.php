<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('university_vice_presidents', function (Blueprint $table) {
            $table->id();
            // Must be a professor (enforced at app layer)
            $table->foreignId('professor_id')
                  ->unique()
                  ->constrained('professors')
                  ->cascadeOnDelete();
            // e.g. "Vice President for Academic Affairs"
            // e.g. "Vice President for Student Affairs"
            // e.g. "Vice President for Research & Development"
            $table->string('title');
            $table->string('title_ar');
            // Controls display order in listings
            $table->unsignedTinyInteger('order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->date('appointed_at');
            $table->date('ended_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('university_vice_presidents');
    }
};
