<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('university', function (Blueprint $table) {
            // Must be a professor (enforced at app layer)
            $table->foreignId('president_id')
                  ->nullable()
                  ->after('logo_path')
                  ->constrained('professors')
                  ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('university', function (Blueprint $table) {
            $table->dropForeign(['president_id']);
            $table->dropColumn('president_id');
        });
    }
};
