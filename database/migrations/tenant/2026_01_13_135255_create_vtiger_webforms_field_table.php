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
        Schema::create('vtiger_webforms_field', function (Blueprint $table) {
            $table->integer('id', true);
            $table->integer('webformid')->index('fk_1_vtiger_webforms_field');
            $table->integer('fieldid')->index('fk_4_vtiger_webforms_field');
            $table->string('fieldname', 50)->index('fk_2_vtiger_webforms_field');
            $table->string('neutralizedfield', 50);
            $table->text('defaultvalue')->nullable();
            $table->integer('required')->default(0);
            $table->integer('sequence')->nullable();
            $table->integer('hidden')->nullable();

            $table->index(['id'], 'webforms_webforms_field_idx');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('vtiger_webforms_field');
    }
};
