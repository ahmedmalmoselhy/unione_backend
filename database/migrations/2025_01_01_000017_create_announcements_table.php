<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('announcements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('author_id')->constrained('users')->restrictOnDelete();
            $table->string('title');
            $table->text('body');
            $table->enum('type', ['general', 'academic', 'administrative', 'urgent']);
            // Controls who can see the announcement
            $table->enum('visibility', ['university', 'faculty', 'department', 'section']);
            // FK to the relevant entity based on visibility:
            //   university  → null (everyone)
            //   faculty     → faculties.id
            //   department  → departments.id
            //   section     → sections.id
            $table->unsignedBigInteger('target_id')->nullable();
            // Null = draft; set to publish
            $table->timestamp('published_at')->nullable();
            $table->timestamp('expires_at')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['visibility', 'target_id']);
            $table->index('published_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('announcements');
    }
};
