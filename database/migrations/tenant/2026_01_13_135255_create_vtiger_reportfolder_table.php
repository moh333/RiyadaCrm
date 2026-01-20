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
        if (Schema::hasTable('vtiger_reportfolder')) {
            return;
        }
        Schema::create('vtiger_reportfolder', function (Blueprint $table) {
            $table->integer('folderid', true);
            $table->string('foldername', 100)->default('');
            $table->string('description', 250)->nullable()->default('');
            $table->string('state', 50)->nullable()->default('SAVED');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('vtiger_reportfolder');
    }
};
