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
        Schema::table('vtiger_projecttaskcf', function (Blueprint $table) {
            $table->foreign(['projecttaskid'], 'fk_projecttaskid_vtiger_projecttaskcf')->references(['projecttaskid'])->on('vtiger_projecttask')->onUpdate('no action')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('vtiger_projecttaskcf', function (Blueprint $table) {
            $table->dropForeign('fk_projecttaskid_vtiger_projecttaskcf');
        });
    }
};
