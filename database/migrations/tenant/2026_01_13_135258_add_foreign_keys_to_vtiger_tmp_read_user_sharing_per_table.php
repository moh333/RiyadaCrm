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
        if (Schema::hasTable('vtiger_tmp_read_user_sharing_per')) {
            return;
        }
        Schema::table('vtiger_tmp_read_user_sharing_per', function (Blueprint $table) {
            $table->foreign(['userid'], 'fk_3_vtiger_tmp_read_user_sharing_per')->references(['id'])->on('vtiger_users')->onUpdate('no action')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('vtiger_tmp_read_user_sharing_per', function (Blueprint $table) {
            $table->dropForeign('fk_3_vtiger_tmp_read_user_sharing_per');
        });
    }
};
