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
        if (Schema::hasTable('vtiger_actionmapping')) {
            return;
        }
        Schema::create('vtiger_actionmapping', function (Blueprint $table) {
            $table->integer('actionid');
            $table->string('actionname', 200);
            $table->integer('securitycheck')->nullable();

            $table->primary(['actionid', 'actionname']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('vtiger_actionmapping');
    }
};
