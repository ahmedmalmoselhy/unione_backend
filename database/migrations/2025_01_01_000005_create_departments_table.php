<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('departments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('faculty_id')->constrained('faculties')->cascadeOnDelete();
            $table->string('name');
            $table->string('name_ar');
            $table->string('code')->unique();
            $table->enum('type', ['academic', 'managerial']);
            // Marks the preparatory/general department for deferred-enrollment faculties
            $table->boolean('is_preparatory')->default(false);
            // head_id added after users; nullable to allow department creation before head assignment
            $table->foreignId('head_id')->nullable()->constrained('users')->nullOnDelete();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('departments');
    }
};
