<?php

namespace App\Modules\Core\VtigerModules\Infrastructure;

use App\Modules\Core\VtigerModules\Contracts\ModuleMetadataRepositoryInterface;
use App\Modules\Core\VtigerModules\Domain\BlockDefinition;
use App\Modules\Core\VtigerModules\Domain\FieldDefinition;
use App\Modules\Core\VtigerModules\Domain\ModuleDefinition;
use App\Modules\Core\VtigerModules\Domain\RelationDefinition;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

/**
 * VtigerModuleMetadataRepository
 * 
 * Concrete implementation that reads vtiger module metadata from database.
 * 
 * This repository handles the complexity of vtiger's schema:
 * - Multiple tables for module definition
 * - EAV-like patterns for custom fields
 * - Complex relationship definitions
 * - Legacy vtiger conventions
 * 
 * Uses raw DB queries (no Eloquent models) to avoid coupling Domain to Laravel.
 */
class VtigerModuleMetadataRepository implements ModuleMetadataRepositoryInterface
{
    private string $connection;

    public function __construct(?string $connection = null)
    {
        $this->connection = $connection ?? config('vtiger-modules.connection', 'tenant');
    }

    /**
     * Load all modules from vtiger_tab
     * 
     * @return Collection<ModuleDefinition>
     */
    public function loadAllModules(): Collection
    {
        $modules = DB::connection($this->connection)
            ->table('vtiger_tab')
            ->where('isentitytype', 1) // Only entity modules
            ->orderBy('name')
            ->get();

        return $modules->map(function ($row) {
            return $this->hydrateModule($row);
        });
    }

    /**
     * Load a single module by name
     * 
     * @param string $name Module name
     * @return ModuleDefinition
     * @throws \InvalidArgumentException
     */
    public function loadModule(string $name): ModuleDefinition
    {
        $row = DB::connection($this->connection)
            ->table('vtiger_tab')
            ->where('name', $name)
            ->where('isentitytype', 1)
            ->first();

        if (!$row) {
            throw new \InvalidArgumentException("Module '{$name}' not found");
        }

        return $this->hydrateModule($row);
    }

    /**
     * Load all fields for a module
     * 
     * @param int $tabId Module ID
     * @return Collection<FieldDefinition>
     */
    public function loadFields(int $tabId): Collection
    {
        $fields = DB::connection($this->connection)
            ->table('vtiger_field')
            ->where('tabid', $tabId)
            ->orderBy('block')
            ->orderBy('sequence')
            ->get();

        return $fields->map(function ($row) use ($tabId) {
            return $this->hydrateField($row, $tabId);
        });
    }

    /**
     * Load all blocks for a module
     * 
     * @param int $tabId Module ID
     * @return Collection<BlockDefinition>
     */
    public function loadBlocks(int $tabId): Collection
    {
        $blocks = DB::connection($this->connection)
            ->table('vtiger_blocks')
            ->where('tabid', $tabId)
            ->orderBy('sequence')
            ->get();

        return $blocks->map(function ($row) use ($tabId) {
            return $this->hydrateBlock($row, $tabId);
        });
    }

    /**
     * Load all relations for a module
     * 
     * @param int $tabId Module ID
     * @return Collection<RelationDefinition>
     */
    public function loadRelations(int $tabId): Collection
    {
        // Get module name for source
        $sourceModule = DB::connection($this->connection)
            ->table('vtiger_tab')
            ->where('tabid', $tabId)
            ->value('name');

        // Load related lists
        $relations = DB::connection($this->connection)
            ->table('vtiger_relatedlists')
            ->where('tabid', $tabId)
            ->orderBy('sequence')
            ->get();

        return $relations->map(function ($row) use ($sourceModule) {
            return $this->hydrateRelation($row, $sourceModule);
        })->filter();
    }

    /**
     * Hydrate ModuleDefinition from database row
     * 
     * @param object $row vtiger_tab row
     * @return ModuleDefinition
     */
    private function hydrateModule(object $row): ModuleDefinition
    {
        // Get base table info from vtiger_entityname
        $entityInfo = DB::connection($this->connection)
            ->table('vtiger_entityname')
            ->where('tabid', $row->tabid)
            ->first();

        $module = ModuleDefinition::create(
            id: (int) $row->tabid,
            name: $row->name,
            label: $row->tablabel ?? $row->name,
            baseTable: $entityInfo->tablename ?? null,
            baseIndex: $entityInfo->entityidfield ?? null,
            isEntity: (bool) $row->isentitytype,
            isCustom: (bool) ($row->customized ?? 0),
            ownedBy: (int) ($row->ownedby ?? 0),
            presence: (int) $row->presence,
        );

        // Load related data
        $module->setFields($this->loadFields($row->tabid));
        $module->setBlocks($this->loadBlocks($row->tabid));
        $module->setRelations($this->loadRelations($row->tabid));

        return $module;
    }

    /**
     * Hydrate FieldDefinition from database row
     * 
     * @param object $row vtiger_field row
     * @param int $tabId Module ID
     * @return FieldDefinition
     */
    private function hydrateField(object $row, int $tabId): FieldDefinition
    {
        // Get module name
        $moduleName = DB::connection($this->connection)
            ->table('vtiger_tab')
            ->where('tabid', $tabId)
            ->value('name');

        return FieldDefinition::create(
            id: (int) $row->fieldid,
            module: $moduleName,
            fieldName: $row->fieldname,
            columnName: $row->columnname,
            tableName: $row->tablename,
            labelEn: $row->fieldlabel_en ?? $row->fieldlabel ?? '', // Handle fallback
            labelAr: $row->fieldlabel_ar ?? null,
            uitype: (int) $row->uitype,
            typeofdata: $row->typeofdata,
            blockId: (int) $row->block,
            presence: (int) $row->presence,
            displayType: (int) $row->displaytype,
            sequence: (int) $row->sequence,
            generatedType: (int) $row->generatedtype,
            defaultValue: $row->defaultvalue,
            maximumLength: $row->maximumlength ? (int) $row->maximumlength : null,
            quickCreate: (bool) $row->quickcreate,
            helpInfo: $row->helpinfo,
            allowMultipleFiles: (bool) ($row->allow_multiple_files ?? false),
            acceptableFileTypes: $row->acceptable_file_types ?? null,
        );
    }

    /**
     * Hydrate BlockDefinition from database row
     * 
     * @param object $row vtiger_blocks row
     * @param int $tabId Module ID
     * @return BlockDefinition
     */
    private function hydrateBlock(object $row, int $tabId): BlockDefinition
    {
        // Get module name
        $moduleName = DB::connection($this->connection)
            ->table('vtiger_tab')
            ->where('tabid', $tabId)
            ->value('name');

        return BlockDefinition::create(
            id: (int) $row->blockid,
            module: $moduleName,
            label: $row->blocklabel,
            sequence: (int) $row->sequence,
            isVisible: (int) $row->visible === 0, // 0 = visible in vtiger
            labelEn: $row->label_en ?? null,
            labelAr: $row->label_ar ?? null,
        );
    }

    /**
     * Hydrate RelationDefinition from database row
     * 
     * @param object $row vtiger_relatedlists row
     * @param string $sourceModule Source module name
     * @return RelationDefinition|null
     */
    private function hydrateRelation(object $row, string $sourceModule): ?RelationDefinition
    {
        // Get target module name
        $targetModule = DB::connection($this->connection)
            ->table('vtiger_tab')
            ->where('tabid', $row->related_tabid)
            ->value('name');

        if (!$targetModule) {
            return null;
        }

        // Parse actions (comma-separated string like "ADD,SELECT")
        $actions = !empty($row->actions) ? explode(',', $row->actions) : [];

        // Determine relation type based on vtiger conventions
        $relationType = $this->determineRelationType($row);

        return RelationDefinition::create(
            sourceModule: $sourceModule,
            targetModule: $targetModule,
            relationType: $relationType,
            relatedField: $row->name ?? null,
            relatedListId: (int) $row->relation_id,
            label: $row->label ?? $targetModule,
            actions: $actions,
            sequence: (int) $row->sequence,
        );
    }

    /**
     * Determine relation type from vtiger_relatedlists row
     * 
     * @param object $row vtiger_relatedlists row
     * @return string "1:N", "N:N", or "lookup"
     */
    private function determineRelationType(object $row): string
    {
        // Check if it's a many-to-many relation (has intermediate table)
        if (!empty($row->name) && str_contains($row->name, 'rel')) {
            return 'N:N';
        }

        // Check if it's a lookup field
        if (!empty($row->name) && !str_contains($row->name, 'id')) {
            return 'lookup';
        }

        // Default to one-to-many
        return '1:N';
    }

    /**
     * Create a new block for a module
     */
    public function addBlock(int $tabId, string $label, ?int $sequence = null, array $data = []): BlockDefinition
    {
        if ($sequence === null) {
            $maxSequence = DB::connection($this->connection)
                ->table('vtiger_blocks')
                ->where('tabid', $tabId)
                ->max('sequence');
            $sequence = $maxSequence ? $maxSequence + 1 : 1;
        } else {
            // Shift existing sequences to make room
            DB::connection($this->connection)
                ->table('vtiger_blocks')
                ->where('tabid', $tabId)
                ->where('sequence', '>', $sequence)
                ->increment('sequence');

            $sequence = $sequence + 1;
        }

        $blockId = DB::connection($this->connection)
            ->table('vtiger_blocks')
            ->insertGetId([
                'tabid' => $tabId,
                'blocklabel' => $label,
                'label_en' => $data['label_en'] ?? null,
                'label_ar' => $data['label_ar'] ?? null,
                'sequence' => $sequence,
                'show_title' => 0,
                'visible' => 0,
                'create_view' => 0,
                'edit_view' => 0,
                'detail_view' => 0,
                'display_status' => 1,
                'iscustom' => 1,
            ], 'blockid');

        $row = DB::connection($this->connection)
            ->table('vtiger_blocks')
            ->where('blockid', $blockId)
            ->first();

        return $this->hydrateBlock($row, $tabId);
    }

    /**
     * Update an existing block
     */
    public function updateBlock(int $blockId, array $data): void
    {
        $updateData = [];
        if (isset($data['label_en']))
            $updateData['label_en'] = $data['label_en'];
        if (isset($data['label_ar']))
            $updateData['label_ar'] = $data['label_ar'];
        if (isset($data['label']))
            $updateData['blocklabel'] = $data['label'];
        if (isset($data['sequence']))
            $updateData['sequence'] = $data['sequence'];

        if (!empty($updateData)) {
            DB::connection($this->connection)
                ->table('vtiger_blocks')
                ->where('blockid', $blockId)
                ->update($updateData);
        }
    }

    /**
     * Delete a block and its fields
     */
    public function deleteBlock(int $blockId): void
    {
        // Delete fields in this block first
        DB::connection($this->connection)
            ->table('vtiger_field')
            ->where('block', $blockId)
            ->delete();

        // Delete the block
        DB::connection($this->connection)
            ->table('vtiger_blocks')
            ->where('blockid', $blockId)
            ->delete();
    }
}
