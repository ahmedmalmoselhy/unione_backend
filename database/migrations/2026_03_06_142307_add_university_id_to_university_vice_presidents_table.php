<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::table('university_vice_presidents', function (Blueprint $table) {
            $table->foreignId('university_id')->constrained('university')->after('id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('university_vice_presidents', function (Blueprint $table) {
            //
        });
    }
};
