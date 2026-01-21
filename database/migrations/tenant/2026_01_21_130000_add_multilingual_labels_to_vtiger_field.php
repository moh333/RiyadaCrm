<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('vtiger_field', function (Blueprint $table) {
            if (!Schema::hasColumn('vtiger_field', 'label_en')) {
                $table->string('label_en', 255)->nullable()->after('fieldlabel');
            }
            if (!Schema::hasColumn('vtiger_field', 'label_ar')) {
                $table->string('label_ar', 255)->nullable()->after('label_en');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('vtiger_field', function (Blueprint $table) {
            $table->dropColumn(['label_en', 'label_ar']);
        });
    }
};
