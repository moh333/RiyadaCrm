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
        if (Schema::hasTable('vtiger_asterisk')) {
            return;
        }
        Schema::create('vtiger_asterisk', function (Blueprint $table) {
            $table->string('server', 30)->nullable();
            $table->string('port', 30)->nullable();
            $table->string('username', 50)->nullable();
            $table->string('password', 50)->nullable();
            $table->string('version', 50)->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('vtiger_asterisk');
    }
};
