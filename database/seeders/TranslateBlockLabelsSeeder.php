<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Stichoza\GoogleTranslate\GoogleTranslate;

class TranslateBlockLabelsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $translations = $this->loadTranslations();
        $tr = new GoogleTranslate('ar');

        // Hardcoded rules as requested
        $hardcodedRules = [
            'LBL_FILE_INFORMATION' => [
                'en' => 'FILE INFORMATION',
                'ar' => 'معلومات الملف'
            ]
        ];

        $blocks = DB::connection('tenant')->table('vtiger_blocks')->get();
        $updatedCount = 0;

        foreach ($blocks as $block) {
            $update = [];
            $blockLabel = $block->blocklabel;

            // 1. Process English label if empty
            if (empty($block->label_en)) {
                if (isset($hardcodedRules[$blockLabel]['en'])) {
                    $update['label_en'] = $hardcodedRules[$blockLabel]['en'];
                } else {
                    // Strip LBL_ and replace underscores with spaces for a decent default
                    $update['label_en'] = str_replace('_', ' ', preg_replace('/^LBL_/', '', $blockLabel));
                }
            }

            // 2. Process Arabic label if empty
            if (empty($block->label_ar)) {
                if (isset($hardcodedRules[$blockLabel]['ar'])) {
                    $update['label_ar'] = $hardcodedRules[$blockLabel]['ar'];
                } elseif (isset($translations[$blockLabel])) {
                    $update['label_ar'] = $translations[$blockLabel];
                } else {
                    try {
                        // Only translate if it looks like a label and not empty
                        if ($blockLabel && !preg_match('/^\d+$/', $blockLabel)) {
                            $englishForTranslation = $update['label_en'] ?? $block->label_en ?: $blockLabel;
                            $update['label_ar'] = $tr->translate($englishForTranslation);
                        }
                    } catch (\Exception $e) {
                        $this->command->error("Failed to translate block '$blockLabel': " . $e->getMessage());
                    }
                }
            }

            if (!empty($update)) {
                DB::connection('tenant')->table('vtiger_blocks')
                    ->where('blockid', $block->blockid)
                    ->update($update);
                $updatedCount++;
            }
        }

        $this->command->info("Updated $updatedCount blocks with labels.");
    }

    private function loadTranslations(): array
    {
        $translations = [];
        $langPath = base_path('app/Modules/Tenant/Resources/Lang/ar/modules');
        if (!File::exists($langPath))
            return [];

        $files = File::allFiles($langPath);
        foreach ($files as $file) {
            if ($file->getExtension() !== 'php')
                continue;
            try {
                $languageStrings = [];
                include $file->getRealPath();
                if (is_array($languageStrings)) {
                    $translations = array_merge($translations, $languageStrings);
                }
            } catch (\Exception $e) {
            }
        }
        return $translations;
    }
}
