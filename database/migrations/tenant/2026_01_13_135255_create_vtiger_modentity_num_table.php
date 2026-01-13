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
        Schema::create('vtiger_modentity_num', function (Blueprint $table) {
            $table->integer('num_id')->unique('num_idx');
            $table->string('semodule', 100)->nullable();
            $table->string('prefix', 50)->default('');
            $table->string('start_id', 50);
            $table->string('cur_id', 50);
            $table->string('active', 2);

            $table->primary(['num_id']);
            $table->index(['semodule', 'active'], 'semodule_active_idx');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('vtiger_modentity_num');
    }
};
