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
        Schema::table('vtiger_projectcf', function (Blueprint $table) {
            $table->foreign(['projectid'], 'fk_projectid_vtiger_projectcf')->references(['projectid'])->on('vtiger_project')->onUpdate('no action')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('vtiger_projectcf', function (Blueprint $table) {
            $table->dropForeign('fk_projectid_vtiger_projectcf');
        });
    }
};
