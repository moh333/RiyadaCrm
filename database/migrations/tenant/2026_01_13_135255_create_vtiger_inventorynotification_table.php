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
        if (Schema::hasTable('vtiger_inventorynotification')) {
            return;
        }
        Schema::create('vtiger_inventorynotification', function (Blueprint $table) {
            $table->integer('notificationid', true);
            $table->string('notificationname', 200)->nullable();
            $table->string('notificationsubject', 200)->nullable();
            $table->text('notificationbody')->nullable();
            $table->string('label', 50)->nullable();
            $table->string('status', 30)->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('vtiger_inventorynotification');
    }
};
