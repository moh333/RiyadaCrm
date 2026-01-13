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
        Schema::create('vtiger_projecttask_status_color', function (Blueprint $table) {
            $table->string('status')->nullable()->unique('status');
            $table->string('defaultcolor', 50)->nullable();
            $table->string('color', 50)->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('vtiger_projecttask_status_color');
    }
};
