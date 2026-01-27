<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('vtiger_role', function (Blueprint $table) {
            $table->integer('copy_from_profile')->nullable()->after('allowassignedrecordsto');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('vtiger_role', function (Blueprint $table) {
            $table->dropColumn('copy_from_profile');
        });
    }
};
