<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (Schema::hasTable('vtiger_inventory_termsandconditions')) {
            return;
        }

        Schema::create('vtiger_inventory_termsandconditions', function (Blueprint $table) {
            $table->increments('id');
            $table->string('module_name', 100)->index();
            $table->text('terms_en')->nullable();
            $table->text('terms_ar')->nullable();
            $table->integer('is_default')->default(0);
            $table->integer('status')->default(1);
            $table->integer('deleted')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('vtiger_inventory_termsandconditions');
    }
};
