<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('departments', function (Blueprint $table) {
            // university = top-level management dept (no faculty parent)
            // faculty    = belongs to a specific faculty
            $table->enum('scope', ['university', 'faculty'])
                  ->default('faculty')
                  ->after('type');

            // University-level departments have no faculty parent
            $table->foreignId('faculty_id')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('departments', function (Blueprint $table) {
            $table->dropColumn('scope');
            $table->foreignId('faculty_id')->nullable(false)->change();
        });
    }
};
