<?php

namespace App\Modules\Core\VtigerModules\Domain;

/**
 * RelationDefinition
 * 
 * Value object representing module relationships.
 * 
 * Represents data from:
 * - vtiger_relatedlists: Related list definitions (1:N relationships)
 * - vtiger_fieldmodulerel: Lookup field relationships
 * 
 * vtiger supports several relation types:
 * - 1:N (One-to-Many): Contact has many Potentials
 * - N:N (Many-to-Many): Contact can be related to many Campaigns
 * - Lookup: Field that references another module
 * 
 * Example relations for Contacts:
 * - Contacts → Potentials (1:N via related_to field)
 * - Contacts → Tickets (1:N via contact_id field)
 * - Contacts → Campaigns (N:N via vtiger_campaigncontrel)
 */
class RelationDefinition
{
    private function __construct(
        private readonly string $sourceModule,
        private readonly string $targetModule,
        private readonly string $relationType,
        private readonly ?string $relatedField,
        private readonly int $relatedListId,
        private readonly string $label,
        private readonly array $actions,
        private readonly int $sequence,
    ) {
    }

    /**
     * Create a new RelationDefinition
     * 
     * @param string $sourceModule Primary module (e.g., "Contacts")
     * @param string $targetModule Related module (e.g., "Potentials")
     * @param string $relationType "1:N", "N:N", or "lookup"
     * @param string|null $relatedField Field linking modules (e.g., "related_to")
     * @param int $relatedListId vtiger_relatedlists.relation_id
     * @param string $label vtiger_relatedlists.label (e.g., "Potentials")
     * @param array $actions Available actions (e.g., ["ADD", "SELECT"])
     * @param int $sequence vtiger_relatedlists.sequence (display order)
     */
    public static function create(
        string $sourceModule,
        string $targetModule,
        string $relationType,
        ?string $relatedField = null,
        int $relatedListId = 0,
        string $label = '',
        array $actions = [],
        int $sequence = 0,
    ): self {
        return new self(
            sourceModule: $sourceModule,
            targetModule: $targetModule,
            relationType: $relationType,
            relatedField: $relatedField,
            relatedListId: $relatedListId,
            label: $label,
            actions: $actions,
            sequence: $sequence,
        );
    }

    // Getters
    public function getSourceModule(): string
    {
        return $this->sourceModule;
    }

    public function getTargetModule(): string
    {
        return $this->targetModule;
    }

    public function getRelationType(): string
    {
        return $this->relationType;
    }

    public function getRelatedField(): ?string
    {
        return $this->relatedField;
    }

    public function getRelatedListId(): int
    {
        return $this->relatedListId;
    }

    public function getLabel(): string
    {
        if (function_exists('vtranslate')) {
            return vtranslate($this->label, $this->targetModule);
        }
        return $this->label;
    }

    public function getActions(): array
    {
        return $this->actions;
    }

    public function getSequence(): int
    {
        return $this->sequence;
    }

    /**
     * Check if this is a many-to-many relationship
     * 
     * @return bool
     */
    public function isManyToMany(): bool
    {
        return $this->relationType === 'N:N';
    }

    /**
     * Check if this is a one-to-many relationship
     * 
     * @return bool
     */
    public function isOneToMany(): bool
    {
        return $this->relationType === '1:N';
    }

    /**
     * Check if this is a lookup field relationship
     * 
     * @return bool
     */
    public function isLookup(): bool
    {
        return $this->relationType === 'lookup';
    }

    /**
     * Check if ADD action is available
     * 
     * @return bool
     */
    public function canAdd(): bool
    {
        return in_array('ADD', $this->actions);
    }

    /**
     * Check if SELECT action is available
     * 
     * @return bool
     */
    public function canSelect(): bool
    {
        return in_array('SELECT', $this->actions);
    }
}
