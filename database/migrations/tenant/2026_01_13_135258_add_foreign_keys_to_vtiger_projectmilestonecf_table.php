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
        Schema::table('vtiger_projectmilestonecf', function (Blueprint $table) {
            $table->foreign(['projectmilestoneid'], 'fk_projectmilestoneid_vtiger_projectmilestonecf')->references(['projectmilestoneid'])->on('vtiger_projectmilestone')->onUpdate('no action')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('vtiger_projectmilestonecf', function (Blueprint $table) {
            $table->dropForeign('fk_projectmilestoneid_vtiger_projectmilestonecf');
        });
    }
};
