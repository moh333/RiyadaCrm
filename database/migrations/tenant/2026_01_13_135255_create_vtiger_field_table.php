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
        if (Schema::hasTable('vtiger_field')) {
            return;
        }
        Schema::create('vtiger_field', function (Blueprint $table) {
            $table->integer('tabid')->index('field_tabid_idx');
            $table->integer('fieldid', true);
            $table->string('columnname', 30);
            $table->string('tablename', 100)->nullable();
            $table->integer('generatedtype')->default(0);
            $table->string('uitype', 30);
            $table->string('fieldname', 50)->index('field_fieldname_idx');
            $table->string('fieldlabel', 50);
            $table->integer('readonly');
            $table->integer('presence')->default(1);
            $table->text('defaultvalue')->nullable();
            $table->integer('maximumlength')->nullable();
            $table->integer('sequence')->nullable();
            $table->integer('block')->nullable()->index('field_block_idx');
            $table->integer('displaytype')->nullable()->index('field_displaytype_idx');
            $table->string('typeofdata', 100)->nullable();
            $table->integer('quickcreate')->default(1);
            $table->integer('quickcreatesequence')->nullable();
            $table->string('info_type', 20)->nullable();
            $table->integer('masseditable')->default(1);
            $table->text('helpinfo')->nullable();
            $table->integer('summaryfield')->default(0);
            $table->integer('headerfield')->nullable()->default(0);
            $table->boolean('isunique')->nullable()->default(false);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('vtiger_field');
    }
};
