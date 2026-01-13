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
        Schema::table('vtiger_user_module_preferences', function (Blueprint $table) {
            $table->foreign(['tabid'], 'fk_2_vtiger_user_module_preferences')->references(['tabid'])->on('vtiger_tab')->onUpdate('cascade')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('vtiger_user_module_preferences', function (Blueprint $table) {
            $table->dropForeign('fk_2_vtiger_user_module_preferences');
        });
    }
};
