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
        if (Schema::hasTable('vtiger_contpotentialrel')) {
            return;
        }
        Schema::create('vtiger_contpotentialrel', function (Blueprint $table) {
            $table->integer('contactid')->default(0)->index('contpotentialrel_contactid_idx');
            $table->integer('potentialid')->default(0)->index('contpotentialrel_potentialid_idx');

            $table->primary(['contactid', 'potentialid']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('vtiger_contpotentialrel');
    }
};
