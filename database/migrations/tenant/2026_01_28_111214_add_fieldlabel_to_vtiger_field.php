<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('vtiger_field', function (Blueprint $table) {
            if (!Schema::hasColumn('vtiger_field', 'fieldlabel')) {
                $table->string('fieldlabel', 255)->nullable()->after('fieldname');
            }
        });

        // Copy values from fieldlabel_en to fieldlabel
        if (Schema::hasColumn('vtiger_field', 'fieldlabel_en')) {
            DB::table('vtiger_field')
                ->whereNotNull('fieldlabel_en')
                ->update([
                    'fieldlabel' => DB::raw('fieldlabel_en')
                ]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('vtiger_field', function (Blueprint $table) {
            if (Schema::hasColumn('vtiger_field', 'fieldlabel')) {
                $table->dropColumn('fieldlabel');
            }
        });
    }
};
