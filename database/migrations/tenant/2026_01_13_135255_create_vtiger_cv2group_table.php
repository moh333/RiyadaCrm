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
        if (Schema::hasTable('vtiger_cv2group')) {
            return;
        }
        Schema::create('vtiger_cv2group', function (Blueprint $table) {
            $table->integer('cvid')->index('vtiger_cv2group_ibfk_1');
            $table->integer('groupid')->index('vtiger_groups_ibfk_1');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('vtiger_cv2group');
    }
};
