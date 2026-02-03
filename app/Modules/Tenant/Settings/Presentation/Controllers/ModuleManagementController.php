<?php

namespace App\Modules\Tenant\Settings\Presentation\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Core\VtigerModules\Contracts\ModuleRegistryInterface;
use App\Modules\Tenant\Contacts\Domain\Enums\CustomFieldType;
use Illuminate\Http\Request;

/**
 * ModuleManagementController
 * 
 * Allows tenants to customize their CRM modules:
 * - Enable/disable modules
 * - Customize field layouts
 * - Configure module numbering
 * - Manage menu visibility
 */
class ModuleManagementController extends Controller
{
    public function __construct(
        private ModuleRegistryInterface $moduleRegistry
    ) {
    }

    /**
     * Show module management dashboard (redirects to modules list)
     */
    public function index()
    {
        return redirect()->route('tenant.settings.modules.list');
    }

    /**
     * 1. Modules List (Active/Inactive)
     */
    public function listModules()
    {
        $modules = $this->moduleRegistry->all();
        return view('tenant::module_mgmt.list', compact('modules'));
    }

    /**
     * 2. Menu Management
     */
    public function menu()
    {
        // Get all modules from registry (unique by name)
        $allModules = collect($this->moduleRegistry->all());

        // Define standard apps to ensure they always show as columns (even if empty)
        $standardApps = ['MARKETING', 'SALES', 'SUPPORT', 'INVENTORY', 'PROJECTS', 'TOOLS', 'OTHERS'];

        // Group modules by their appName
        $grouped = $allModules->groupBy(fn($m) => $m->getAppName());

        // Build the final collection in the specific order, ensuring all standard apps exist
        $groupedModules = collect();
        foreach ($standardApps as $app) {
            $groupedModules[$app] = $grouped->get($app, collect());
        }

        // Add any additional apps found in the database that aren't in standard list
        foreach ($grouped as $app => $mods) {
            if (!$groupedModules->has($app)) {
                $groupedModules[$app] = $mods;
            }
        }

        return view('tenant::module_mgmt.menu', compact('groupedModules'));
    }

    public function updateMenu(Request $request)
    {
        $validated = $request->validate([
            'apps' => 'required|array',
            'apps.*.name' => 'required|string',
            'apps.*.modules' => 'nullable|array',
            'apps.*.modules.*.tabid' => 'required|integer',
            'apps.*.modules.*.sequence' => 'required|integer',
            'apps.*.modules.*.visible' => 'nullable|boolean',
        ]);

        \DB::connection('tenant')->transaction(function () use ($validated) {
            $globalSequence = 1;

            foreach ($validated['apps'] as $appData) {
                $appName = $appData['name'];
                $modules = $appData['modules'] ?? [];

                // Sort modules by the sequence sent from frontend to be safe
                usort($modules, fn($a, $b) => $a['sequence'] <=> $b['sequence']);

                foreach ($modules as $modIndex => $modData) {
                    $tabId = $modData['tabid'];
                    $presence = (isset($modData['visible']) && ($modData['visible'] == "1" || $modData['visible'] === true)) ? 0 : 1;

                    // 1. Update global vtiger_tab entry
                    \DB::connection('tenant')
                        ->table('vtiger_tab')
                        ->where('tabid', $tabId)
                        ->update([
                            'tabsequence' => $globalSequence++,
                            'presence' => $presence
                        ]);

                    // 2. Update app-specific mapping
                    if ($appName !== 'OTHERS') {
                        \DB::connection('tenant')
                            ->table('vtiger_app2tab')
                            ->updateOrInsert(
                                ['tabid' => $tabId],
                                [
                                    'appname' => $appName,
                                    'sequence' => $modIndex,
                                    'visible' => $presence === 0 ? 1 : 0
                                ]
                            );
                    } else {
                        // If moved to OTHERS, remove app assignment so it defaults to OTHERS in loads
                        \DB::connection('tenant')
                            ->table('vtiger_app2tab')
                            ->where('tabid', $tabId)
                            ->delete();
                    }
                }
            }
        });

        $this->moduleRegistry->refresh();

        return redirect()->route('tenant.settings.modules.menu')
            ->with('success', __('tenant::tenant.updated_successfully'));
    }

    /**
     * 3. Module Layouts & Fields (Selection Page)
     */
    public function layouts()
    {
        $modules = $this->moduleRegistry->getActive();
        return view('tenant::module_mgmt.layouts_selection', compact('modules'));
    }

    /**
     * Show module layout editor
     */
    public function editLayout(string $module)
    {
        $moduleDefinition = $this->moduleRegistry->get($module);
        $fieldTypes = array_map(
            fn($type) => $type,
            CustomFieldType::getCustomFieldTypes()
        );

        return view('tenant::module_mgmt.layout', compact('moduleDefinition', 'fieldTypes'));
    }

    /**
     * Update module layout
     */
    public function updateLayout(Request $request, string $module)
    {
        $moduleDefinition = $this->moduleRegistry->get($module);
        $allFieldsData = $request->input('fields', []);

        foreach ($moduleDefinition->fields() as $fieldDef) {
            $fieldId = $fieldDef->getId();
            $settings = $allFieldsData[$fieldId] ?? [];

            $dbField = \DB::connection('tenant')
                ->table('vtiger_field')
                ->where('fieldid', $fieldId)
                ->first();

            if (!$dbField)
                continue;

            // Correct presence logic: 1 is hidden, 0 (core) or 2 (custom) is visible
            $presence = isset($settings['visible'])
                ? ($dbField->presence == 1 ? ($dbField->generatedtype == 1 ? 0 : 2) : $dbField->presence)
                : 1;

            // displaytype: 1 = editable, 3 = readonly
            $displaytype = isset($settings['editable']) ? 1 : 3;

            // typeofdata: condition is M (Mandatory) or O (Optional)
            $typeofdata = $dbField->typeofdata;
            if (str_contains($typeofdata, '~')) {
                $parts = explode('~', $typeofdata);
                if (count($parts) >= 2) {
                    $parts[1] = isset($settings['mandatory']) ? 'M' : 'O';
                    $typeofdata = implode('~', $parts);
                }
            }

            \DB::connection('tenant')
                ->table('vtiger_field')
                ->where('fieldid', $fieldId)
                ->update([
                    'presence' => $presence,
                    'displaytype' => $displaytype,
                    'typeofdata' => $typeofdata,
                ]);
        }

        $this->moduleRegistry->refresh();

        return redirect()
            ->route('tenant.settings.modules.layout', $module)
            ->with('success', __('tenant::tenant.updated_successfully'));
    }

    /**
     * Update field order and block (Drag & Drop)
     */
    public function updateFieldOrder(Request $request, string $module)
    {
        $validated = $request->validate([
            'fields' => 'required|array',
            'fields.*.field_id' => 'required|integer',
            'fields.*.block_id' => 'required|integer',
            'fields.*.sequence' => 'required|integer',
        ]);

        \DB::connection('tenant')->transaction(function () use ($validated) {
            foreach ($validated['fields'] as $fieldData) {
                \DB::connection('tenant')
                    ->table('vtiger_field')
                    ->where('fieldid', $fieldData['field_id'])
                    ->update([
                        'block' => $fieldData['block_id'],
                        'sequence' => $fieldData['sequence'],
                    ]);
            }
        });

        $this->moduleRegistry->refresh();

        return response()->json(['success' => true]);
    }

    /**
     * Add a new block to a module
     */
    public function addBlock(Request $request, string $module)
    {
        $moduleDefinition = $this->moduleRegistry->get($module);
        $validated = $request->validate([
            'label_en' => 'required|string|max:100',
            'label_ar' => 'required|string|max:100',
            'after_block' => 'nullable|integer',
        ]);

        // Use repository to add block
        $repository = app(\App\Modules\Core\VtigerModules\Contracts\ModuleMetadataRepositoryInterface::class);

        $repository->addBlock(
            $moduleDefinition->getId(),
            $validated['label_en'], // Using EN as the base label/key
            $validated['after_block'] ? (int) $validated['after_block'] : null,
            [
                'label_en' => $validated['label_en'],
                'label_ar' => $validated['label_ar'],
            ]
        );

        $this->moduleRegistry->refresh();

        return redirect()
            ->route('tenant.settings.modules.layout', $module)
            ->with('success', __('tenant::tenant.created_successfully'));
    }

    /**
     * Update an existing block
     */
    public function updateBlock(Request $request, string $module, int $blockId)
    {
        $validated = $request->validate([
            'label_en' => 'required|string|max:100',
            'label_ar' => 'required|string|max:100',
        ]);

        $repository = app(\App\Modules\Core\VtigerModules\Contracts\ModuleMetadataRepositoryInterface::class);
        $repository->updateBlock($blockId, [
            'label' => $validated['label_en'],
            'label_en' => $validated['label_en'],
            'label_ar' => $validated['label_ar'],
        ]);

        $this->moduleRegistry->refresh();

        return redirect()
            ->route('tenant.settings.modules.layout', $module)
            ->with('success', __('tenant::tenant.updated_successfully'));
    }

    /**
     * Delete a block and its fields
     */
    public function deleteBlock(string $module, int $blockId)
    {
        $repository = app(\App\Modules\Core\VtigerModules\Contracts\ModuleMetadataRepositoryInterface::class);
        $repository->deleteBlock($blockId);

        $this->moduleRegistry->refresh();

        return redirect()
            ->route('tenant.settings.modules.layout', $module)
            ->with('success', __('tenant::tenant.deleted_successfully'));
    }

    /**
     * 3. Module Numbering (Selection Page)
     */
    public function numbering()
    {
        $modules = $this->moduleRegistry->getActive();
        return view('tenant::module_mgmt.numbering_selection', compact('modules'));
    }

    /**
     * Show module numbering configuration
     */
    public function editNumbering(string $module)
    {
        $moduleDefinition = $this->moduleRegistry->get($module);

        $numberingConfig = \DB::connection('tenant')
            ->table('vtiger_modentity_num')
            ->where('semodule', $module)
            ->first();

        return view('tenant::module_mgmt.numbering', compact('moduleDefinition', 'numberingConfig'));
    }

    /**
     * Update module numbering
     */
    public function updateNumbering(Request $request, string $module)
    {
        $validated = $request->validate([
            'prefix' => 'required|string|max:10',
            'start_id' => 'required|integer|min:1',
        ]);

        // Check if configuration already exists
        \DB::connection('tenant')->transaction(function () use ($module, $validated) {
            $existing = \DB::connection('tenant')
                ->table('vtiger_modentity_num')
                ->where('semodule', $module)
                ->first();

            if ($existing) {
                // Update existing configuration
                \DB::connection('tenant')
                    ->table('vtiger_modentity_num')
                    ->where('semodule', $module)
                    ->update([
                        'prefix' => $validated['prefix'],
                        'start_id' => $validated['start_id'],
                        // Only update cur_id if new start_id is greater than current cur_id
                        // This prevents going backwards but allows increasing the sequence
                        'cur_id' => \DB::raw('GREATEST(cur_id, ' . $validated['start_id'] . ')'),
                        'active' => 1,
                    ]);
            } else {
                // Insert new configuration
                \DB::connection('tenant')
                    ->table('vtiger_modentity_num')
                    ->insert([
                        'semodule' => $module,
                        'prefix' => $validated['prefix'],
                        'start_id' => $validated['start_id'],
                        'cur_id' => $validated['start_id'],
                        'active' => 1,
                    ]);
            }
        });

        return redirect()
            ->route('tenant.settings.modules.numbering', $module)
            ->with('success', __('tenant::tenant.updated_successfully'));
    }

    /**
     * Toggle module status
     */
    public function toggleStatus(string $module)
    {
        $moduleDefinition = $this->moduleRegistry->get($module);
        $newPresence = $moduleDefinition->getPresence() === 0 ? 1 : 0;

        \DB::connection('tenant')
            ->table('vtiger_tab')
            ->where('tabid', $moduleDefinition->getId())
            ->update(['presence' => $newPresence]);

        $this->moduleRegistry->refresh();

        return redirect()
            ->route('tenant.settings.modules.list')
            ->with('success', __('tenant::tenant.updated_successfully'));
    }

    /**
     * 4. Module Relations (Selection Page)
     */
    public function relationsSelection()
    {
        $modules = $this->moduleRegistry->getActive();
        return view('tenant::module_mgmt.relations_selection', compact('modules'));
    }

    /**
     * Show module relations management
     */
    public function editRelations(string $module)
    {
        $moduleDefinition = $this->moduleRegistry->get($module);

        // 1. One-many & Many-many Relationships (Related Lists)
        // Using leftJoin to include relations where related_tabid is 0 or doesn't exist in vtiger_tab
        $relatedLists = \DB::connection('tenant')
            ->table('vtiger_relatedlists as vrl')
            ->leftJoin('vtiger_tab as vt', 'vrl.related_tabid', '=', 'vt.tabid')
            ->where('vrl.tabid', $moduleDefinition->getId())
            ->select([
                'vrl.relation_id',
                'vrl.related_tabid',
                'vrl.name',
                'vrl.sequence',
                'vrl.label',
                'vrl.actions',
                'vrl.relationtype',
                'vrl.presence',
                'vt.name as target_module_name',
                'vt.tablabel as target_module_label'
            ])
            ->orderBy('vrl.sequence')
            ->get();

        // 2. One-one & Many-one Relationships (Lookup Fields)
        $lookupFields = \DB::connection('tenant')
            ->table('vtiger_field as vf')
            ->where('vf.tabid', $moduleDefinition->getId())
            ->whereIn('vf.uitype', [10, 51, 57, 58, 59, 73, 75, 76, 77, 78, 80, 81, 101, 117])
            ->where('vf.presence', '!=', 1)
            ->select([
                'vf.fieldid',
                'vf.fieldname',
                'vf.fieldlabel',
                'vf.uitype'
            ])
            ->get();

        foreach ($lookupFields as $field) {
            $field->related_modules = \DB::connection('tenant')
                ->table('vtiger_fieldmodulerel')
                ->where('fieldid', $field->fieldid)
                ->pluck('relmodule')
                ->toArray();
        }

        // Get all available modules for adding new relations
        $availableModules = $this->moduleRegistry->getActive()
            ->filter(fn($m) => $m->getName() !== $module);

        return view('tenant::module_mgmt.relations', compact(
            'moduleDefinition',
            'relatedLists',
            'lookupFields',
            'availableModules'
        ));
    }

    /**
     * Store new relation
     */
    public function storeRelation(Request $request, string $module)
    {
        $moduleDefinition = $this->moduleRegistry->get($module);

        $validated = $request->validate([
            'target_module' => 'required|string',
            'label' => 'required|string|max:100',
            'relation_type' => 'required|in:1:N,N:N',
            'actions' => 'nullable|array',
            'actions.*' => 'in:ADD,SELECT',
        ]);

        $targetModule = $this->moduleRegistry->get($validated['target_module']);

        // Generate next relation ID
        $nextRelationId = $this->getNextRelationId();

        // Get next sequence number
        $maxSequence = \DB::connection('tenant')
            ->table('vtiger_relatedlists')
            ->where('tabid', $moduleDefinition->getId())
            ->max('sequence') ?? 0;

        // Prepare actions string
        $actionsString = !empty($validated['actions']) ? implode(',', $validated['actions']) : '';

        // Insert relation
        \DB::connection('tenant')->table('vtiger_relatedlists')->insert([
            'relation_id' => $nextRelationId,
            'tabid' => $moduleDefinition->getId(),
            'related_tabid' => $targetModule->getId(),
            'name' => $validated['target_module'],
            'sequence' => $maxSequence + 1,
            'label' => $validated['label'],
            'presence' => 0,
            'actions' => $actionsString,
            'relationtype' => $validated['relation_type'],
        ]);

        $this->moduleRegistry->refresh();

        return redirect()
            ->route('tenant.settings.modules.relations', $module)
            ->with('success', __('tenant::tenant.created_successfully'));
    }

    /**
     * Update existing relation
     */
    public function updateRelation(Request $request, string $module, int $relationId)
    {
        $validated = $request->validate([
            'label' => 'required|string|max:100',
            'actions' => 'nullable|array',
            'actions.*' => 'in:ADD,SELECT',
        ]);

        // Prepare actions string
        $actionsString = !empty($validated['actions']) ? implode(',', $validated['actions']) : '';

        \DB::connection('tenant')
            ->table('vtiger_relatedlists')
            ->where('relation_id', $relationId)
            ->update([
                'label' => $validated['label'],
                'actions' => $actionsString,
            ]);

        $this->moduleRegistry->refresh();

        return redirect()
            ->route('tenant.settings.modules.relations', $module)
            ->with('success', __('tenant::tenant.updated_successfully'));
    }

    /**
     * Delete relation
     */
    public function deleteRelation(string $module, int $relationId)
    {
        // Soft delete by setting presence to 1
        \DB::connection('tenant')
            ->table('vtiger_relatedlists')
            ->where('relation_id', $relationId)
            ->update(['presence' => 1]);

        $this->moduleRegistry->refresh();

        return redirect()
            ->route('tenant.settings.modules.relations', $module)
            ->with('success', __('tenant::tenant.deleted_successfully'));
    }

    /**
     * Reorder relations
     */
    public function reorderRelations(Request $request, string $module)
    {
        $validated = $request->validate([
            'relations' => 'required|array',
            'relations.*.relation_id' => 'required|integer',
            'relations.*.sequence' => 'required|integer',
        ]);

        \DB::connection('tenant')->transaction(function () use ($validated) {
            foreach ($validated['relations'] as $relation) {
                \DB::connection('tenant')
                    ->table('vtiger_relatedlists')
                    ->where('relation_id', $relation['relation_id'])
                    ->update(['sequence' => $relation['sequence']]);
            }
        });

        $this->moduleRegistry->refresh();

        return response()->json(['success' => true]);
    }

    /**
     * Generate next relation ID from sequence table
     */
    private function getNextRelationId(): int
    {
        $query = \DB::connection('tenant')->table('vtiger_relatedlists_seq')->lockForUpdate();
        $result = $query->first();

        if (!$result) {
            $maxId = \DB::connection('tenant')->table('vtiger_relatedlists')->max('relation_id') ?? 1000;
            $nextId = $maxId + 1;
            \DB::connection('tenant')->table('vtiger_relatedlists_seq')->insert(['id' => $nextId]);
            return $nextId;
        }

        $nextId = $result->id + 1;
        \DB::connection('tenant')->table('vtiger_relatedlists_seq')->update(['id' => $nextId]);

        return $nextId;
    }
}
