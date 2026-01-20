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
        if (Schema::hasTable('vtiger_crmentity')) {
            return;
        }
        Schema::create('vtiger_crmentity', function (Blueprint $table) {
            $table->integer('crmid')->primary();
            $table->integer('smcreatorid')->default(0)->index('crmentity_smcreatorid_idx');
            $table->integer('smownerid')->default(0);
            $table->integer('modifiedby')->default(0)->index('crmentity_modifiedby_idx');
            $table->string('setype', 100)->nullable();
            $table->mediumText('description')->nullable();
            $table->dateTime('createdtime');
            $table->dateTime('modifiedtime');
            $table->dateTime('viewedtime')->nullable();
            $table->string('status', 50)->nullable();
            $table->integer('version')->default(0);
            $table->integer('presence')->nullable()->default(1);
            $table->integer('deleted')->default(0)->index('crmentity_deleted_idx');
            $table->integer('smgroupid')->nullable();
            $table->string('source', 100)->nullable();
            $table->string('label')->nullable()->index('vtiger_crmentity_labelidx');

            $table->index(['smownerid', 'deleted', 'setype'], 'crm_ownerid_del_setype_idx');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('vtiger_crmentity');
    }
};
