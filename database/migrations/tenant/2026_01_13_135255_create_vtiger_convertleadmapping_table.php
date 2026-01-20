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
        if (Schema::hasTable('vtiger_convertleadmapping')) {
            return;
        }
        Schema::create('vtiger_convertleadmapping', function (Blueprint $table) {
            $table->integer('cfmid', true);
            $table->integer('leadfid');
            $table->integer('accountfid')->nullable();
            $table->integer('contactfid')->nullable();
            $table->integer('potentialfid')->nullable();
            $table->integer('editable')->nullable()->default(1);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('vtiger_convertleadmapping');
    }
};
