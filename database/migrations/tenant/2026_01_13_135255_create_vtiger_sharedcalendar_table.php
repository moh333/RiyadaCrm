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
        if (Schema::hasTable('vtiger_sharedcalendar')) {
            return;
        }
        Schema::create('vtiger_sharedcalendar', function (Blueprint $table) {
            $table->integer('userid');
            $table->integer('sharedid');

            $table->primary(['userid', 'sharedid']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('vtiger_sharedcalendar');
    }
};
