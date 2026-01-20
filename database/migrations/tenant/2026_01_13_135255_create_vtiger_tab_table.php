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
        if (Schema::hasTable('vtiger_tab')) {
            return;
        }
        Schema::create('vtiger_tab', function (Blueprint $table) {
            $table->integer('tabid')->default(0)->primary();
            $table->string('name', 25)->unique('tab_name_idx');
            $table->integer('presence')->default(1);
            $table->integer('tabsequence')->nullable();
            $table->string('tablabel', 100)->nullable();
            $table->integer('modifiedby')->nullable()->index('tab_modifiedby_idx');
            $table->integer('modifiedtime')->nullable();
            $table->integer('customized')->nullable();
            $table->integer('ownedby')->nullable();
            $table->integer('isentitytype')->default(1);
            $table->integer('trial')->default(0);
            $table->string('version', 10)->nullable();
            $table->string('parent', 30)->nullable();
            $table->string('source')->nullable()->default('custom');
            $table->boolean('issyncable')->nullable()->default(false);
            $table->boolean('allowduplicates')->nullable()->default(true);
            $table->integer('sync_action_for_duplicates')->nullable()->default(1);

            $table->index(['tabid'], 'tab_tabid_idx');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('vtiger_tab');
    }
};
