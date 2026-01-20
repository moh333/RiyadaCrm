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
        if (Schema::hasTable('vtiger_customerportal_prefs')) {
            return;
        }
        Schema::create('vtiger_customerportal_prefs', function (Blueprint $table) {
            $table->integer('tabid');
            $table->string('prefkey', 100);
            $table->integer('prefvalue')->nullable();

            $table->primary(['tabid', 'prefkey']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('vtiger_customerportal_prefs');
    }
};
