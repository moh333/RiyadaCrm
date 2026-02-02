<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Stichoza\GoogleTranslate\GoogleTranslate;

class TranslateFieldLabelsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $translations = $this->loadTranslations();
        $tr = new GoogleTranslate('ar'); // Target language: Arabic

        if (empty($translations)) {
            $this->command->warn('No local translations found. Will rely entirely on Google Translate API.');
        } else {
            $this->command->info('Loaded ' . count($translations) . ' translation keys from local files.');
        }

        // Get all fields
        $fields = DB::connection('tenant')->table('vtiger_field')->get();
        $updatedCount = 0;
        $apiTranslatedCount = 0;

        foreach ($fields as $field) {
            $label = $field->fieldlabel_en ?: $field->fieldlabel;
            $translated = null;

            // 1. Try local dictionary first
            if (isset($translations[$label])) {
                $translated = $translations[$label];
            }
            // 2. Fallback to Google Translate if dictionary check fails and it's not already translated
            elseif (empty($field->fieldlabel_ar) || $field->fieldlabel_ar === $label) {
                try {
                    // Skip labels that are obviously just IDs (like cf_123)
                    if (!preg_match('/^cf_\d+$/', $label)) {
                        $translated = $tr->translate($label);
                        $apiTranslatedCount++;

                        // Small delay to be polite to the free API
                        if ($apiTranslatedCount % 10 === 0)
                            usleep(500000);
                    }
                } catch (\Exception $e) {
                    $this->command->error("Failed to translate '$label' via API: " . $e->getMessage());
                }
            }

            if ($translated) {
                DB::connection('tenant')->table('vtiger_field')
                    ->where('fieldid', $field->fieldid)
                    ->update([
                        'fieldlabel_ar' => $translated
                    ]);
                $updatedCount++;
            }
        }

        $this->command->info("Task complete.");
        $this->command->info("- Total updated: $updatedCount");
        $this->command->info("- Local matches: " . ($updatedCount - $apiTranslatedCount));
        $this->command->info("- API translated: $apiTranslatedCount");
    }

    /**
     * Load all Arabic translations from the module language files.
     */
    private function loadTranslations(): array
    {
        $translations = [];
        $langPath = base_path('app/Modules/Tenant/Resources/Lang/ar/modules');

        if (!File::exists($langPath)) {
            return [];
        }

        $files = File::allFiles($langPath);
        foreach ($files as $file) {
            if ($file->getExtension() !== 'php')
                continue;

            try {
                // We use a separate function to isolate the scope of the included file
                $strings = $this->getLanguageStrings($file->getRealPath());
                if (is_array($strings)) {
                    // Merge, allowing later files to overwrite if there are collisions
                    // (Vtiger.php is usually the base, specific modules are specific)
                    $translations = array_merge($translations, $strings);
                }
            } catch (\Exception $e) {
                // Skip problematic files
            }
        }

        return $translations;
    }

    /**
     * Safely include the language file and return languageStrings.
     */
    private function getLanguageStrings(string $path): array
    {
        $languageStrings = [];
        include $path;
        return $languageStrings;
    }
}
