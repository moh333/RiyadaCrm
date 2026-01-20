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
        if (Schema::hasTable('vtiger_webform_file_fields')) {
            return;
        }
        Schema::create('vtiger_webform_file_fields', function (Blueprint $table) {
            $table->integer('id', true);
            $table->integer('webformid')->index('fk_vtiger_webforms');
            $table->string('fieldname', 100);
            $table->string('fieldlabel', 100);
            $table->integer('required')->default(0);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('vtiger_webform_file_fields');
    }
};
