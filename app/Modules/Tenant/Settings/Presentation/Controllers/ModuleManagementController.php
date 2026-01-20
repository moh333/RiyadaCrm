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
     * 2. Module Layouts & Fields (Selection Page)
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
        $modules = $this->moduleRegistry->all();
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

        \DB::connection('tenant')
            ->table('vtiger_modentity_num')
            ->updateOrInsert(
                ['semodule' => $module],
                [
                    'prefix' => $validated['prefix'],
                    'start_id' => $validated['start_id'],
                    'cur_id' => \DB::raw('COALESCE(cur_id, ' . $validated['start_id'] . ')'),
                    'active' => 1,
                ]
            );

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
}
