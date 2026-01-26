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
        Schema::table('vtiger_groups', function (Blueprint $table) {
            if (!Schema::hasColumn('vtiger_groups', 'allow_ticket_assign')) {
                $table->tinyInteger('allow_ticket_assign')->default(0);
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('vtiger_groups', function (Blueprint $table) {
            $table->dropColumn('allow_ticket_assign');
        });
    }
};
