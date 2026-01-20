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
        if (Schema::hasTable('vtiger_defaulteventstatus')) {
            return;
        }
        Schema::create('vtiger_defaulteventstatus', function (Blueprint $table) {
            $table->integer('defaulteventstatusid', true);
            $table->string('defaulteventstatus', 200);
            $table->integer('presence')->default(1);
            $table->integer('picklist_valueid')->default(0);
            $table->integer('sortorderid')->nullable()->default(0);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('vtiger_defaulteventstatus');
    }
};
