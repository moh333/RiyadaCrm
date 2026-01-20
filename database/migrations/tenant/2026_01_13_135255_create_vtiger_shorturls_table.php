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
        if (Schema::hasTable('vtiger_shorturls')) {
            return;
        }
        Schema::create('vtiger_shorturls', function (Blueprint $table) {
            $table->integer('id', true);
            $table->string('uid', 50)->nullable()->index('uid');
            $table->string('handler_path', 400)->nullable();
            $table->string('handler_class', 100)->nullable();
            $table->string('handler_function', 100)->nullable();
            $table->text('handler_data')->nullable();
            $table->integer('onetime')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('vtiger_shorturls');
    }
};
