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
        Schema::create('vtiger_user_module_preferences', function (Blueprint $table) {
            $table->integer('userid');
            $table->integer('tabid')->index('fk_2_vtiger_user_module_preferences');
            $table->integer('default_cvid');

            $table->primary(['userid', 'tabid']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('vtiger_user_module_preferences');
    }
};
