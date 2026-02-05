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
        if (Schema::connection('tenant')->hasTable('vtiger_configuration_editor')) {
            return;
        }

        Schema::connection('tenant')->create('vtiger_configuration_editor', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique();
            $table->text('value')->nullable();
            $table->timestamps();
        });

        // Seed default values from the Edit View
        $defaults = [
            ['key' => 'default_module', 'value' => 'Dashboard'],
            ['key' => 'max_entries_per_page', 'value' => '20'],
            ['key' => 'max_text_length', 'value' => '50'],
            ['key' => 'max_upload_size', 'value' => '5'],
            ['key' => 'allowed_file_types', 'value' => 'pdf,doc,docx,xls,xlsx,jpg,png,gif'],
            ['key' => 'helpdesk_support_email', 'value' => 'support@example.com'],
            ['key' => 'helpdesk_support_name', 'value' => 'Support Team'],
            ['key' => 'show_icons', 'value' => '1'],
            ['key' => 'show_colors', 'value' => '1'],
            ['key' => 'compact_view', 'value' => '0'],
        ];

        DB::connection('tenant')->table('vtiger_configuration_editor')->insert(
            array_map(function ($item) {
                return array_merge($item, [
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }, $defaults)
        );
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::connection('tenant')->dropIfExists('vtiger_configuration_editor');
    }
};
