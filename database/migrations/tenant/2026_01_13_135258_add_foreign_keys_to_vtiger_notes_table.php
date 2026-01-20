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
        if (Schema::hasTable('vtiger_notes')) {
            return;
        }
        Schema::table('vtiger_notes', function (Blueprint $table) {
            $table->foreign(['notesid'], 'fk_1_vtiger_notes')->references(['crmid'])->on('vtiger_crmentity')->onUpdate('no action')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('vtiger_notes', function (Blueprint $table) {
            $table->dropForeign('fk_1_vtiger_notes');
        });
    }
};
