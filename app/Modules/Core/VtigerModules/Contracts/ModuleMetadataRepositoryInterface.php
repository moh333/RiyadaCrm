<?php

namespace App\Modules\Core\VtigerModules\Contracts;

use App\Modules\Core\VtigerModules\Domain\BlockDefinition;
use App\Modules\Core\VtigerModules\Domain\FieldDefinition;
use App\Modules\Core\VtigerModules\Domain\ModuleDefinition;
use App\Modules\Core\VtigerModules\Domain\RelationDefinition;
use Illuminate\Support\Collection;

/**
 * ModuleMetadataRepositoryInterface
 * 
 * Data access contract for loading vtiger module metadata.
 * 
 * Implementations read from vtiger database tables:
 * - vtiger_tab (modules)
 * - vtiger_field (fields)
 * - vtiger_blocks (field groups)
 * - vtiger_relatedlists (relationships)
 * - vtiger_entityname (base table info)
 * - vtiger_fieldmodulerel (lookup relations)
 * 
 * This interface abstracts the complexity of vtiger's schema.
 */
interface ModuleMetadataRepositoryInterface
{
    /**
     * Load all modules from vtiger_tab
     * 
     * Includes fields, blocks, and relations for each module
     * 
     * @return Collection<ModuleDefinition>
     */
    public function loadAllModules(): Collection;

    /**
     * Load a single module by name
     * 
     * Includes fields, blocks, and relations
     * 
     * @param string $name Module name (e.g., "Contacts")
     * @return ModuleDefinition
     * @throws \InvalidArgumentException if module not found
     */
    public function loadModule(string $name): ModuleDefinition;

    /**
     * Load all fields for a module
     * 
     * @param int $tabId Module ID (vtiger_tab.tabid)
     * @return Collection<FieldDefinition>
     */
    public function loadFields(int $tabId): Collection;

    /**
     * Load all blocks for a module
     * 
     * @param int $tabId Module ID (vtiger_tab.tabid)
     * @return Collection<BlockDefinition>
     */
    public function loadBlocks(int $tabId): Collection;

    /**
     * Load all relations for a module
     * 
     * @param int $tabId Module ID (vtiger_tab.tabid)
     * @return Collection<RelationDefinition>
     */
    public function loadRelations(int $tabId): Collection;

    /**
     * Create a new block for a module
     * 
     * @param int $tabId Module ID
     * @param string $label Block label
     * @param int|null $sequence Optional sequence
     * @param array $data Additional data (label_en, label_ar)
     * @return BlockDefinition
     */
    public function addBlock(int $tabId, string $label, ?int $sequence = null, array $data = []): BlockDefinition;

    /**
     * Update an existing block
     * 
     * @param int $blockId
     * @param array $data (label_en, label_ar, sequence)
     * @return void
     */
    public function updateBlock(int $blockId, array $data): void;

    /**
     * Delete a block
     * 
     * @param int $blockId
     * @return void
     */
    public function deleteBlock(int $blockId): void;
}
