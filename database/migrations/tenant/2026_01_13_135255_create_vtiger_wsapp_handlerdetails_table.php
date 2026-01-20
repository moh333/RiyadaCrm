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
        if (Schema::hasTable('vtiger_wsapp_handlerdetails')) {
            return;
        }
        Schema::create('vtiger_wsapp_handlerdetails', function (Blueprint $table) {
            $table->string('type', 200);
            $table->string('handlerclass', 100)->nullable();
            $table->string('handlerpath', 300)->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('vtiger_wsapp_handlerdetails');
    }
};
