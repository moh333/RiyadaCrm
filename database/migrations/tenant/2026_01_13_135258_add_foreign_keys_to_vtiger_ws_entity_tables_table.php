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
        Schema::table('vtiger_ws_entity_tables', function (Blueprint $table) {
            $table->foreign(['webservice_entity_id'], 'fk_1_vtiger_ws_actor_tables')->references(['id'])->on('vtiger_ws_entity')->onUpdate('no action')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('vtiger_ws_entity_tables', function (Blueprint $table) {
            $table->dropForeign('fk_1_vtiger_ws_actor_tables');
        });
    }
};
