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
        if (Schema::hasTable('vtiger_faqcategories')) {
            return;
        }
        Schema::create('vtiger_faqcategories', function (Blueprint $table) {
            $table->integer('faqcategories_id', true);
            $table->string('faqcategories', 200)->nullable();
            $table->integer('presence')->default(1);
            $table->integer('picklist_valueid')->default(0);
            $table->integer('sortorderid')->nullable();
            $table->string('color', 10)->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('vtiger_faqcategories');
    }
};
