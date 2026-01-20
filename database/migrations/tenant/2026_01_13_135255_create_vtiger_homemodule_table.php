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
        if (Schema::hasTable('vtiger_homemodule')) {
            return;
        }
        Schema::create('vtiger_homemodule', function (Blueprint $table) {
            $table->integer('stuffid')->primary();
            $table->string('modulename', 100)->nullable();
            $table->integer('maxentries');
            $table->integer('customviewid');
            $table->string('setype', 30);

            $table->index(['stuffid'], 'stuff_stuffid_idx');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('vtiger_homemodule');
    }
};
