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
        Schema::table('vtiger_users2group', function (Blueprint $table) {
            $table->foreign(['userid'], 'fk_2_vtiger_users2group')->references(['id'])->on('vtiger_users')->onUpdate('no action')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('vtiger_users2group', function (Blueprint $table) {
            $table->dropForeign('fk_2_vtiger_users2group');
        });
    }
};
