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
        if (Schema::hasTable('vtiger_datashare_relatedmodule_permission')) {
            return;
        }
        Schema::create('vtiger_datashare_relatedmodule_permission', function (Blueprint $table) {
            $table->integer('shareid');
            $table->integer('datashare_relatedmodule_id');
            $table->integer('permission')->nullable();

            $table->index(['shareid', 'permission'], 'datashare_relatedmodule_permission_shareid_permissions_idx');
            $table->primary(['shareid', 'datashare_relatedmodule_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('vtiger_datashare_relatedmodule_permission');
    }
};
