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
        if (Schema::hasTable('vtiger_users')) {
            return;
        }
        Schema::create('vtiger_users', function (Blueprint $table) {
            $table->integer('id', true);
            $table->string('user_name')->nullable()->index('user_user_name_idx');
            $table->string('user_password', 200)->nullable()->index('user_user_password_idx');
            $table->string('cal_color', 25)->nullable()->default('#E6FAD8');
            $table->string('first_name', 30)->nullable();
            $table->string('last_name', 30)->nullable();
            $table->string('reports_to_id', 36)->nullable();
            $table->string('is_admin', 3)->nullable()->default('0');
            $table->integer('currency_id')->default(1);
            $table->text('description')->nullable();
            $table->timestamp('date_entered');
            $table->dateTime('date_modified')->nullable();
            $table->string('modified_user_id', 36)->nullable();
            $table->string('title', 50)->nullable();
            $table->string('department', 50)->nullable();
            $table->string('phone_home', 50)->nullable();
            $table->string('phone_mobile', 50)->nullable();
            $table->string('phone_work', 50)->nullable();
            $table->string('phone_other', 50)->nullable();
            $table->string('phone_fax', 50)->nullable();
            $table->string('email1', 100)->nullable();
            $table->string('email2', 100)->nullable();
            $table->string('secondaryemail', 100)->nullable();
            $table->string('status', 25)->nullable();
            $table->text('signature')->nullable();
            $table->string('address_street', 150)->nullable();
            $table->string('address_city', 100)->nullable();
            $table->string('address_state', 100)->nullable();
            $table->string('address_country', 25)->nullable();
            $table->string('address_postalcode', 9)->nullable();
            $table->text('user_preferences')->nullable();
            $table->string('tz', 30)->nullable();
            $table->string('holidays', 60)->nullable();
            $table->string('namedays', 60)->nullable();
            $table->string('workdays', 30)->nullable();
            $table->integer('weekstart')->nullable();
            $table->string('date_format', 200)->nullable();
            $table->string('hour_format', 30)->nullable()->default('am/pm');
            $table->string('start_hour', 30)->nullable()->default('10:00');
            $table->string('end_hour', 30)->nullable()->default('23:00');
            $table->string('is_owner', 100)->nullable()->default('0');
            $table->string('activity_view', 200)->nullable()->default('Today');
            $table->string('lead_view', 200)->nullable()->default('Today');
            $table->string('imagename', 250)->nullable();
            $table->integer('deleted')->default(0);
            $table->string('confirm_password', 300)->nullable();
            $table->string('internal_mailer', 3)->default('1');
            $table->string('reminder_interval', 100)->nullable();
            $table->string('reminder_next_time', 100)->nullable();
            $table->string('crypt_type', 20)->default('MD5');
            $table->string('accesskey', 36)->nullable();
            $table->string('theme', 100)->nullable();
            $table->string('language', 36)->nullable();
            $table->string('time_zone', 200)->nullable();
            $table->string('currency_grouping_pattern', 100)->nullable();
            $table->string('currency_decimal_separator', 2)->nullable();
            $table->string('currency_grouping_separator', 2)->nullable();
            $table->string('currency_symbol_placement', 20)->nullable();
            $table->string('userlabel')->nullable();
            $table->string('defaultlandingpage', 200)->nullable();
            $table->string('phone_crm_extension', 100)->nullable();
            $table->string('no_of_currency_decimals', 2)->nullable();
            $table->string('truncate_trailing_zeros', 3)->nullable();
            $table->string('dayoftheweek', 100)->nullable();
            $table->string('callduration', 100)->nullable();
            $table->string('othereventduration', 100)->nullable();
            $table->string('calendarsharedtype', 100)->nullable();
            $table->string('default_record_view', 10)->nullable();
            $table->string('leftpanelhide', 3)->nullable();
            $table->string('rowheight', 10)->nullable();
            $table->string('defaulteventstatus', 50)->nullable();
            $table->string('defaultactivitytype', 50)->nullable();
            $table->integer('hidecompletedevents')->nullable();
            $table->string('defaultcalendarview', 100)->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('vtiger_users');
    }
};
