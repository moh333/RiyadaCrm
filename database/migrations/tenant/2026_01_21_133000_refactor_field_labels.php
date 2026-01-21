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
            // 1. Remove the label_en and label_ar columns (if they exist from previous attempt)
            $colsToRemove = [];
            if (Schema::hasColumn('vtiger_field', 'label_en'))
                $colsToRemove[] = 'label_en';
            if (Schema::hasColumn('vtiger_field', 'label_ar'))
                $colsToRemove[] = 'label_ar';
            if (!empty($colsToRemove)) {
                $table->dropColumn($colsToRemove);
            }

            // 2. Rename fieldlabel to fieldlabel_en
            // Note: Schema::renameColumn requires doctrine/dbal or is native in Laravel 8.8+ / PHP 8+
            if (Schema::hasColumn('vtiger_field', 'fieldlabel') && !Schema::hasColumn('vtiger_field', 'fieldlabel_en')) {
                $table->renameColumn('fieldlabel', 'fieldlabel_en');
            }

            // 3. Add fieldlabel_ar column after fieldlabel_en
            if (!Schema::hasColumn('vtiger_field', 'fieldlabel_ar')) {
                $table->string('fieldlabel_ar', 255)->nullable()->after('fieldlabel_en');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('vtiger_field', function (Blueprint $table) {
            if (Schema::hasColumn('vtiger_field', 'fieldlabel_en') && !Schema::hasColumn('vtiger_field', 'fieldlabel')) {
                $table->renameColumn('fieldlabel_en', 'fieldlabel');
            }
            $table->dropColumn(['fieldlabel_ar']);
            $table->string('label_en', 255)->nullable()->after('fieldlabel');
            $table->string('label_ar', 255)->nullable()->after('label_en');
        });
    }
};
