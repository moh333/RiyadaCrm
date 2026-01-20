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
        if (Schema::hasTable('vtiger_user2role')) {
            return;
        }
        Schema::table('vtiger_user2role', function (Blueprint $table) {
            $table->foreign(['userid'], 'fk_2_vtiger_user2role')->references(['id'])->on('vtiger_users')->onUpdate('no action')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('vtiger_user2role', function (Blueprint $table) {
            $table->dropForeign('fk_2_vtiger_user2role');
        });
    }
};
