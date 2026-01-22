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
            if (!Schema::hasColumn('vtiger_field', 'allow_multiple_files')) {
                $table->boolean('allow_multiple_files')->default(false)->after('helpinfo');
            }
            if (!Schema::hasColumn('vtiger_field', 'acceptable_file_types')) {
                $table->text('acceptable_file_types')->nullable()->after('allow_multiple_files');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('vtiger_field', function (Blueprint $table) {
            if (Schema::hasColumn('vtiger_field', 'allow_multiple_files')) {
                $table->dropColumn('allow_multiple_files');
            }
            if (Schema::hasColumn('vtiger_field', 'acceptable_file_types')) {
                $table->dropColumn('acceptable_file_types');
            }
        });
    }
};
