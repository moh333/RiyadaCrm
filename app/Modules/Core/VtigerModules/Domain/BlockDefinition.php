<?php

namespace App\Modules\Core\VtigerModules\Domain;

/**
 * BlockDefinition
 * 
 * Value object representing a block in vtiger_blocks table.
 * 
 * vtiger_blocks groups fields into logical sections (blocks) in forms.
 * For example, Contacts module has blocks like:
 * - "LBL_CONTACT_INFORMATION" (name, email, phone)
 * - "LBL_ADDRESS_INFORMATION" (street, city, country)
 * - "LBL_DESCRIPTION_INFORMATION" (notes, description)
 * 
 * Key concepts:
 * - blocklabel: Translation key for block title
 * - sequence: Display order of blocks
 * - visible: 0=visible, 1=hidden
 */
class BlockDefinition
{
    private function __construct(
        private readonly int $id,
        private readonly string $module,
        private readonly string $label,
        private readonly int $sequence,
        private readonly bool $isVisible,
        private readonly ?string $labelEn = null,
        private readonly ?string $labelAr = null,
    ) {
    }

    /**
     * Create a new BlockDefinition
     * 
     * @param int $id vtiger_blocks.blockid
     * @param string $module Module name
     * @param string $label vtiger_blocks.blocklabel (e.g., "LBL_CONTACT_INFORMATION")
     * @param int $sequence vtiger_blocks.sequence (display order)
     * @param bool $isVisible vtiger_blocks.visible = 0 (0=visible, 1=hidden)
     * @param string|null $labelEn English label
     * @param string|null $labelAr Arabic label
     */
    public static function create(
        int $id,
        string $module,
        string $label,
        int $sequence = 0,
        bool $isVisible = true,
        ?string $labelEn = null,
        ?string $labelAr = null,
    ): self {
        return new self(
            id: $id,
            module: $module,
            label: $label,
            sequence: $sequence,
            isVisible: $isVisible,
            labelEn: $labelEn,
            labelAr: $labelAr,
        );
    }

    // Getters
    public function getId(): int
    {
        return $this->id;
    }

    public function getModule(): string
    {
        return $this->module;
    }

    public function getLabel(): string
    {
        $locale = app()->getLocale();
        if ($locale === 'ar' && $this->labelAr) {
            return $this->labelAr;
        }
        if ($locale === 'en' && $this->labelEn) {
            return $this->labelEn;
        }

        if (function_exists('vtranslate')) {
            return vtranslate($this->label, $this->module);
        }
        return $this->label;
    }

    public function getSequence(): int
    {
        return $this->sequence;
    }

    /**
     * Check if block should be shown
     * 
     * @return bool
     */
    public function isVisible(): bool
    {
        return $this->isVisible;
    }

    public function getLabelEn(): ?string
    {
        return $this->labelEn;
    }

    public function getLabelAr(): ?string
    {
        return $this->labelAr;
    }
}
