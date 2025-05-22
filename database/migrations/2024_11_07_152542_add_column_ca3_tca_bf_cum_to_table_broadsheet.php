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
        Schema::table('broadsheet', function (Blueprint $table) {
                $table->double('ca3',5, 2)->after('ca2')->default('0');
                $table->double('tca',5, 2)->after('ca3')->nullable(); // Add your new column here
                $table->double('bf',5, 2)->after('total')->nullable(); // Add your new column here
                $table->double('cum',5, 2)->after('bf')->nullable(); // Add your new column here
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('broadsheet', function (Blueprint $table) {
            $table->dropColumn('ca3'); // Drop the column if the migration is rolled back
            $table->dropColumn('tca');
            $table->dropColumn('bf');
            $table->dropColumn('cum');
        });
    }
};
