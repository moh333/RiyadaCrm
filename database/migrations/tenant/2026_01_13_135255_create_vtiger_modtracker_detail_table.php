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
        if (Schema::hasTable('vtiger_modtracker_detail')) {
            return;
        }
        Schema::create('vtiger_modtracker_detail', function (Blueprint $table) {
            $table->integer('id')->nullable()->index('idx');
            $table->string('fieldname', 100)->nullable();
            $table->text('prevalue')->nullable();
            $table->text('postvalue')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('vtiger_modtracker_detail');
    }
};
