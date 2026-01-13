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
        Schema::table('vtiger_webforms_field', function (Blueprint $table) {
            $table->foreign(['webformid'], 'fk_1_vtiger_webforms_field')->references(['id'])->on('vtiger_webforms')->onUpdate('no action')->onDelete('cascade');
            $table->foreign(['fieldid'], 'fk_4_vtiger_webforms_field')->references(['fieldid'])->on('vtiger_field')->onUpdate('no action')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('vtiger_webforms_field', function (Blueprint $table) {
            $table->dropForeign('fk_1_vtiger_webforms_field');
            $table->dropForeign('fk_4_vtiger_webforms_field');
        });
    }
};
