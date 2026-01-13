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
        Schema::create('vtiger_salesmanticketrel', function (Blueprint $table) {
            $table->integer('smid')->default(0)->index('salesmanticketrel_smid_idx');
            $table->integer('id')->default(0)->index('salesmanticketrel_id_idx');

            $table->primary(['smid', 'id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('vtiger_salesmanticketrel');
    }
};
