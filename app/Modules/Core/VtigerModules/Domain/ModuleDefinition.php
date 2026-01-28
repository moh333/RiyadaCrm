<?php

namespace App\Modules\Core\VtigerModules\Domain;

use Illuminate\Support\Collection;

/**
 * ModuleDefinition
 * 
 * Rich domain entity representing a vtiger CRM module.
 * 
 * Represents metadata from:
 * - vtiger_tab: Module registration table
 * - vtiger_entityname: Base table information
 * 
 * This is the root aggregate for module metadata, containing:
 * - Fields (vtiger_field)
 * - Blocks (vtiger_blocks)
 * - Relations (vtiger_relatedlists)
 */
class ModuleDefinition
{
    private function __construct(
        private readonly int $id,
        private readonly string $name,
        private readonly string $label,
        private readonly ?string $baseTable,
        private readonly ?string $baseIndex,
        private readonly bool $isEntity,
        private readonly bool $isCustom,
        private readonly int $ownedBy,
        private readonly int $presence,
        private readonly string $appName = 'OTHERS',
        private Collection $fields,
        private Collection $blocks,
        private Collection $relations,
    ) {
    }

    /**
     * Create a new ModuleDefinition
     * 
     * @param int $id vtiger_tab.tabid
     * @param string $name vtiger_tab.name (e.g., "Contacts")
     * @param string $label vtiger_tab.tablabel (e.g., "LBL_CONTACTS")
     * @param string|null $baseTable vtiger_entityname.tablename (e.g., "vtiger_contactdetails")
     * @param string|null $baseIndex vtiger_entityname.entityidfield (e.g., "contactid")
     * @param bool $isEntity Has crmentity record (vtiger_entityname exists)
     * @param bool $isCustom vtiger_tab.customized = 1
     * @param int $ownedBy vtiger_tab.ownedby (0=all users, 1=specific users)
     * @param int $presence vtiger_tab.presence (0=visible, 1=hidden, 2=readonly)
     */
    public static function create(
        int $id,
        string $name,
        string $label,
        ?string $baseTable = null,
        ?string $baseIndex = null,
        bool $isEntity = true,
        bool $isCustom = false,
        int $ownedBy = 0,
        int $presence = 0,
        string $appName = 'OTHERS',
    ): self {
        return new self(
            id: $id,
            name: $name,
            label: $label,
            baseTable: $baseTable,
            baseIndex: $baseIndex,
            isEntity: $isEntity,
            isCustom: $isCustom,
            ownedBy: $ownedBy,
            presence: $presence,
            appName: $appName,
            fields: collect(),
            blocks: collect(),
            relations: collect(),
        );
    }

    // Getters
    public function getId(): int
    {
        return $this->id;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getLabel(): string
    {
        return $this->label;
    }

    public function getBaseTable(): ?string
    {
        return $this->baseTable;
    }

    public function getBaseIndex(): ?string
    {
        return $this->baseIndex;
    }

    public function isEntity(): bool
    {
        return $this->isEntity;
    }

    public function isCustom(): bool
    {
        return $this->isCustom;
    }

    public function getOwnedBy(): int
    {
        return $this->ownedBy;
    }

    public function getPresence(): int
    {
        return $this->presence;
    }

    public function getAppName(): string
    {
        return $this->appName;
    }

    /**
     * Get all fields for this module
     * 
     * @return Collection<FieldDefinition>
     */
    public function fields(): Collection
    {
        return $this->fields;
    }

    /**
     * Get all blocks for this module
     * 
     * @return Collection<BlockDefinition>
     */
    public function blocks(): Collection
    {
        return $this->blocks;
    }

    /**
     * Get all relations for this module
     * 
     * @return Collection<RelationDefinition>
     */
    public function relations(): Collection
    {
        return $this->relations;
    }

    /**
     * Find a field by name
     * 
     * @param string $name Field name (e.g., "email", "firstname")
     * @return FieldDefinition|null
     */
    public function getField(string $name): ?FieldDefinition
    {
        return $this->fields->first(fn(FieldDefinition $field) => $field->getFieldName() === $name);
    }

    /**
     * Find a block by ID
     * 
     * @param int $id Block ID
     * @return BlockDefinition|null
     */
    public function getBlock(int $id): ?BlockDefinition
    {
        return $this->blocks->first(fn(BlockDefinition $block) => $block->getId() === $id);
    }

    /**
     * Find a relation to a target module
     * 
     * @param string $moduleName Target module name
     * @return RelationDefinition|null
     */
    public function getRelation(string $moduleName): ?RelationDefinition
    {
        return $this->relations->first(fn(RelationDefinition $rel) => $rel->getTargetModule() === $moduleName);
    }

    /**
     * Check if module is active (visible)
     * 
     * @return bool
     */
    public function isActive(): bool
    {
        return $this->presence === 0;
    }

    /**
     * Set fields for this module
     * 
     * @param Collection<FieldDefinition> $fields
     */
    public function setFields(Collection $fields): void
    {
        $this->fields = $fields;
    }

    /**
     * Set blocks for this module
     * 
     * @param Collection<BlockDefinition> $blocks
     */
    public function setBlocks(Collection $blocks): void
    {
        $this->blocks = $blocks;
    }

    /**
     * Set relations for this module
     * 
     * @param Collection<RelationDefinition> $relations
     */
    public function setRelations(Collection $relations): void
    {
        $this->relations = $relations;
    }

    /**
     * Get fields grouped by block
     * 
     * @return Collection<int, Collection<FieldDefinition>>
     */
    public function getFieldsByBlock(): Collection
    {
        return $this->fields->groupBy(fn(FieldDefinition $field) => $field->getBlockId());
    }

    /**
     * Get only custom fields
     * 
     * @return Collection<FieldDefinition>
     */
    public function getCustomFields(): Collection
    {
        return $this->fields->filter(fn(FieldDefinition $field) => $field->isCustomField());
    }

    /**
     * Get only visible fields
     * 
     * @return Collection<FieldDefinition>
     */
    public function getVisibleFields(): Collection
    {
        return $this->fields->filter(fn(FieldDefinition $field) => $field->isVisible());
    }

    /**
     * Get only editable fields
     * 
     * @return Collection<FieldDefinition>
     */
    public function getEditableFields(): Collection
    {
        return $this->fields->filter(fn(FieldDefinition $field) => $field->isEditable());
    }
}
