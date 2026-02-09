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
        // Check if the table already exists
        if (Schema::connection('tenant')->hasTable('vtiger_schedulereports')) {
            // Add missing columns if they don't exist
            Schema::connection('tenant')->table('vtiger_schedulereports', function (Blueprint $table) {
                if (!Schema::connection('tenant')->hasColumn('vtiger_schedulereports', 'scheduleid')) {
                    $table->tinyInteger('scheduleid')->default(1)->after('reportid');
                }
                if (!Schema::connection('tenant')->hasColumn('vtiger_schedulereports', 'schdayoftheweek')) {
                    $table->json('schdayoftheweek')->nullable()->after('schtime');
                }
                if (!Schema::connection('tenant')->hasColumn('vtiger_schedulereports', 'schdayofthemonth')) {
                    $table->json('schdayofthemonth')->nullable()->after('schdayoftheweek');
                }
                if (!Schema::connection('tenant')->hasColumn('vtiger_schedulereports', 'schannualdates')) {
                    $table->json('schannualdates')->nullable()->after('schdayofthemonth');
                }
                if (!Schema::connection('tenant')->hasColumn('vtiger_schedulereports', 'schdate')) {
                    $table->json('schdate')->nullable()->after('schannualdates');
                }
                if (!Schema::connection('tenant')->hasColumn('vtiger_schedulereports', 'recipients')) {
                    $table->json('recipients')->nullable()->after('schdate');
                }
                if (!Schema::connection('tenant')->hasColumn('vtiger_schedulereports', 'specificemails')) {
                    $table->json('specificemails')->nullable()->after('recipients');
                }
                if (!Schema::connection('tenant')->hasColumn('vtiger_schedulereports', 'fileformat')) {
                    $table->string('fileformat', 10)->default('CSV')->after('specificemails');
                }
                if (!Schema::connection('tenant')->hasColumn('vtiger_schedulereports', 'next_trigger_time')) {
                    $table->dateTime('next_trigger_time')->nullable()->after('fileformat');
                }
            });
        } else {
            // Create the table from scratch
            Schema::connection('tenant')->create('vtiger_schedulereports', function (Blueprint $table) {
                $table->unsignedBigInteger('reportid')->primary();
                $table->tinyInteger('scheduleid')->default(1)->comment('1=Daily, 2=Weekly, 3=Monthly, 4=Annually, 5=Specific Date');
                $table->time('schtime')->default('09:00:00');
                $table->json('schdayoftheweek')->nullable()->comment('Array of weekday numbers for weekly schedule');
                $table->json('schdayofthemonth')->nullable()->comment('Array of day numbers for monthly schedule');
                $table->json('schannualdates')->nullable()->comment('Array of dates for annual schedule');
                $table->json('schdate')->nullable()->comment('Specific date(s) for one-time schedule');
                $table->json('recipients')->nullable()->comment('Array of USER::id, GROUP::id, ROLE::id');
                $table->json('specificemails')->nullable()->comment('Array of specific email addresses');
                $table->string('fileformat', 10)->default('CSV');
                $table->dateTime('next_trigger_time')->nullable();

                $table->foreign('reportid')
                    ->references('reportid')
                    ->on('vtiger_report')
                    ->onDelete('cascade');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Note: We don't drop the table in down() as it may be a pre-existing Vtiger table
        // Instead, just remove the columns we added
        if (Schema::connection('tenant')->hasTable('vtiger_schedulereports')) {
            Schema::connection('tenant')->table('vtiger_schedulereports', function (Blueprint $table) {
                $columns = [
                    'scheduleid',
                    'schdayoftheweek',
                    'schdayofthemonth',
                    'schannualdates',
                    'schdate',
                    'recipients',
                    'specificemails',
                    'fileformat',
                    'next_trigger_time'
                ];

                foreach ($columns as $column) {
                    if (Schema::connection('tenant')->hasColumn('vtiger_schedulereports', $column)) {
                        $table->dropColumn($column);
                    }
                }
            });
        }
    }
};
