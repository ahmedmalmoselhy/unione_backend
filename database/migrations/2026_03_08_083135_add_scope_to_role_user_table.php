<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('role_user', function (Blueprint $table) {
            $table->foreignId('faculty_id')
                  ->nullable()
                  ->after('role_id')
                  ->constrained('faculties')
                  ->nullOnDelete();
            $table->foreignId('department_id')
                  ->nullable()
                  ->after('faculty_id')
                  ->constrained('departments')
                  ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('role_user', function (Blueprint $table) {
            $table->dropConstrainedForeignId('faculty_id');
            $table->dropConstrainedForeignId('department_id');
        });
    }
};
