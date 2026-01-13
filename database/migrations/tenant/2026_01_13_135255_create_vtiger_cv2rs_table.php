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
        Schema::create('vtiger_cv2rs', function (Blueprint $table) {
            $table->integer('cvid')->index('vtiger_cv2role_ibfk_1');
            $table->string('rsid')->index('vtiger_rolesd_ibfk_1');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('vtiger_cv2rs');
    }
};
