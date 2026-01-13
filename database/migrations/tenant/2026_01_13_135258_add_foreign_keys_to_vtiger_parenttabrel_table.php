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
        Schema::table('vtiger_parenttabrel', function (Blueprint $table) {
            $table->foreign(['tabid'], 'fk_1_vtiger_parenttabrel')->references(['tabid'])->on('vtiger_tab')->onUpdate('no action')->onDelete('cascade');
            $table->foreign(['parenttabid'], 'fk_2_vtiger_parenttabrel')->references(['parenttabid'])->on('vtiger_parenttab')->onUpdate('no action')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('vtiger_parenttabrel', function (Blueprint $table) {
            $table->dropForeign('fk_1_vtiger_parenttabrel');
            $table->dropForeign('fk_2_vtiger_parenttabrel');
        });
    }
};
