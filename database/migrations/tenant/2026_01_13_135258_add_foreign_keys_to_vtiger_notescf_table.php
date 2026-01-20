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
        if (Schema::hasTable('vtiger_notescf')) {
            return;
        }
        Schema::table('vtiger_notescf', function (Blueprint $table) {
            $table->foreign(['notesid'], 'fk_notesid_vtiger_notescf')->references(['notesid'])->on('vtiger_notes')->onUpdate('no action')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('vtiger_notescf', function (Blueprint $table) {
            $table->dropForeign('fk_notesid_vtiger_notescf');
        });
    }
};
