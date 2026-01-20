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
        if (Schema::hasTable('vtiger_fieldmodulerel')) {
            return;
        }
        Schema::create('vtiger_fieldmodulerel', function (Blueprint $table) {
            $table->integer('fieldid');
            $table->string('module', 100);
            $table->string('relmodule', 100);
            $table->string('status', 10)->nullable();
            $table->integer('sequence')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('vtiger_fieldmodulerel');
    }
};
