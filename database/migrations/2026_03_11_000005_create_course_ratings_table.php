<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('course_ratings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('enrollment_id')->constrained()->cascadeOnDelete();
            $table->unsignedTinyInteger('rating');  // 1-5
            $table->text('comment')->nullable();
            $table->timestamp('rated_at');
            $table->timestamps();

            $table->unique('enrollment_id');        // one rating per enrollment
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('course_ratings');
    }
};
