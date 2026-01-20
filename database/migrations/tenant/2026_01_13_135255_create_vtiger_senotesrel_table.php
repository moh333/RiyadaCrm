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
        if (Schema::hasTable('vtiger_senotesrel')) {
            return;
        }
        Schema::create('vtiger_senotesrel', function (Blueprint $table) {
            $table->integer('crmid')->default(0)->index('senotesrel_crmid_idx');
            $table->integer('notesid')->default(0)->index('senotesrel_notesid_idx');

            $table->primary(['crmid', 'notesid']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('vtiger_senotesrel');
    }
};
