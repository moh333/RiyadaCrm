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
        Schema::create('vtiger_default_record_view', function (Blueprint $table) {
            $table->integer('default_record_viewid', true);
            $table->string('default_record_view', 200);
            $table->integer('sortorderid')->nullable();
            $table->integer('presence')->default(1);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('vtiger_default_record_view');
    }
};
