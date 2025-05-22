<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('classcategories', function (Blueprint $table) {
            $table->string('ca3score')->after('ca2score')->nullable(); // Add your new column here
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('classcategories', function (Blueprint $table) {
            $table->dropColumn('ca3score'); // Drop the column if the migration is rolled back
        });
    }
};
