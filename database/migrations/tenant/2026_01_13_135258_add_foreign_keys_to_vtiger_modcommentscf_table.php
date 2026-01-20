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
        if (Schema::hasTable('vtiger_modcommentscf')) {
            return;
        }
        Schema::table('vtiger_modcommentscf', function (Blueprint $table) {
            $table->foreign(['modcommentsid'], 'fk_modcommentsid_vtiger_modcommentscf')->references(['modcommentsid'])->on('vtiger_modcomments')->onUpdate('no action')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('vtiger_modcommentscf', function (Blueprint $table) {
            $table->dropForeign('fk_modcommentsid_vtiger_modcommentscf');
        });
    }
};
