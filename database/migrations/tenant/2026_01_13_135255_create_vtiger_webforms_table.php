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
        if (Schema::hasTable('vtiger_webforms')) {
            return;
        }
        Schema::create('vtiger_webforms', function (Blueprint $table) {
            $table->integer('id', true);
            $table->string('name', 100)->unique('webformname');
            $table->string('publicid', 100);
            $table->integer('enabled')->default(1);
            $table->string('targetmodule', 50);
            $table->text('description')->nullable();
            $table->integer('ownerid');
            $table->string('returnurl', 250)->nullable();
            $table->integer('captcha')->default(0);
            $table->integer('roundrobin')->default(0);
            $table->string('roundrobin_userid', 256)->nullable();
            $table->integer('roundrobin_logic')->default(0);

            $table->unique(['id'], 'publicid');
            $table->index(['id'], 'webforms_webforms_id_idx');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('vtiger_webforms');
    }
};
