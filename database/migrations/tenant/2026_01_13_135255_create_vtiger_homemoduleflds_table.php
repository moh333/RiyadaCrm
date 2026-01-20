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
        if (Schema::hasTable('vtiger_homemoduleflds')) {
            return;
        }
        Schema::create('vtiger_homemoduleflds', function (Blueprint $table) {
            $table->integer('stuffid')->nullable()->index('stuff_stuffid_idx');
            $table->string('fieldname', 100)->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('vtiger_homemoduleflds');
    }
};
