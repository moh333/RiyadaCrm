<?php

namespace App\Modules\Tenant\Contacts\Presentation\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Tenant\Contacts\Application\DTOs\CreateCustomFieldDTO;
use App\Modules\Tenant\Contacts\Application\UseCases\CreateCustomFieldUseCase;
use App\Modules\Tenant\Contacts\Application\UseCases\DeleteCustomFieldUseCase;
use App\Modules\Tenant\Contacts\Domain\Enums\CustomFieldType;
use App\Modules\Tenant\Contacts\Domain\Repositories\CustomFieldRepositoryInterface;
use Illuminate\Http\Request;

/**
 * CustomFieldsController
 * 
 * Manages custom field definitions for any module
 */
class CustomFieldsController extends Controller
{
    public function __construct(
        private CustomFieldRepositoryInterface $customFieldRepository,
        private CreateCustomFieldUseCase $createCustomFieldUseCase,
        private DeleteCustomFieldUseCase $deleteCustomFieldUseCase,
        private \App\Modules\Tenant\Contacts\Application\UseCases\UpdateCustomFieldUseCase $updateCustomFieldUseCase,
        private \App\Modules\Core\VtigerModules\Services\ModuleRegistry $moduleRegistry,
    ) {
    }

    /**
     * Display list of custom fields
     */
    public function index(string $module)
    {
        $moduleInfo = $this->getModuleInfo($module);
        if (!$moduleInfo) {
            abort(404, "Module $module not found");
        }

        $customFields = $this->customFieldRepository->findByModule((int) $moduleInfo->tabid);

        return view('contacts_module::custom-fields.index', [
            'customFields' => $customFields,
            'module' => $module,
            'moduleInfo' => $moduleInfo,
        ]);
    }

    /**
     * Show form to create new custom field
     */
    public function create(string $module, Request $request)
    {
        $moduleInfo = $this->getModuleInfo($module);
        if (!$moduleInfo) {
            abort(404, "Module $module not found");
        }

        // Get only field types suitable for custom field creation
        $fieldTypes = array_map(
            fn($type) => $type,
            CustomFieldType::getCustomFieldTypes()
        );

        // Get available blocks for this module
        $blocks = $this->getModuleBlocks((int) $moduleInfo->tabid);

        return view('contacts_module::custom-fields.create', [
            'fieldTypes' => $fieldTypes,
            'blocks' => $blocks,
            'module' => $module,
            'moduleInfo' => $moduleInfo,
            'selectedBlockId' => $request->query('block_id'),
        ]);
    }

    public function store(Request $request, string $module)
    {
        $moduleInfo = $this->getModuleInfo($module);
        if (!$moduleInfo) {
            abort(404, "Module $module not found");
        }

        $validated = $request->validate([
            'fieldname' => 'required|string|max:50|regex:/^[a-zA-Z0-9_]+$/',
            'fieldlabel_en' => 'required|string|max:100',
            'fieldlabel_ar' => 'nullable|string|max:100',
            'uitype' => 'required|integer',
            'block' => 'required|integer',
            'quickcreate' => 'boolean',
            'helpinfo' => 'nullable|string',
            'defaultvalue' => 'nullable',
            'picklist_values' => 'nullable|string',
            'role_based_picklist' => 'nullable|boolean',
            'length' => 'nullable|integer',
            'decimal_places' => 'nullable|integer',
            'allow_multiple_files' => 'nullable|boolean',
            'acceptable_file_types' => 'nullable|string',
        ]);

        try {
            $uitypeValue = (int) $validated['uitype'];
            $uitypeEnum = CustomFieldType::from($uitypeValue);

            // If uitype is 15 (PICKLIST) and role_based_picklist is NOT checked,
            // change it to 16 (PICKLIST_READONLY - non-role-based)
            if ($uitypeValue == 15 && !($request->input('role_based_picklist', false))) {
                $validated['uitype'] = 16;
                $uitypeValue = 16;
                $uitypeEnum = CustomFieldType::PICKLIST_READONLY;
            }

            // Generate typeofdata
            $typeofdata = $uitypeEnum->getTypeOfData(); // e.g., "V~O"
            $parts = explode('~', $typeofdata);
            if (isset($validated['length'])) {
                $config = $validated['length'];
                if (isset($validated['decimal_places']) && $uitypeValue == 71) {
                    $config .= ',' . $validated['decimal_places'];
                }
                $parts[2] = $config;
            }
            $validated['typeofdata'] = implode('~', $parts);

            // Handle default value (could be array from multiselect)
            if (isset($validated['defaultvalue'])) {
                if (is_array($validated['defaultvalue'])) {
                    $validated['defaultvalue'] = '|##|' . implode('|##|', $validated['defaultvalue']) . '|##|';
                } elseif (!empty($validated['defaultvalue']) && $uitypeValue == 33) {
                    $validated['defaultvalue'] = '|##|' . $validated['defaultvalue'] . '|##|';
                }
            }

            $dto = CreateCustomFieldDTO::fromRequest(array_merge($validated, [
                'tabid' => $moduleInfo->tabid,
                'module_name' => $module,
            ]));

            $this->createCustomFieldUseCase->execute($dto);
            $this->moduleRegistry->refresh();

            return redirect()
                ->route('tenant.settings.modules.layout', $module)
                ->with('success', __('contacts::contacts.custom_field_created'));
        } catch (\DomainException $e) {
            return back()
                ->withInput()
                ->withErrors(['error' => $e->getMessage()]);
        }
    }

    /**
     * Delete custom field
     */
    public function destroy(string $module, int $id)
    {
        try {
            $this->deleteCustomFieldUseCase->execute($id);
            $this->moduleRegistry->refresh();

            return redirect()
                ->route('tenant.settings.modules.layout', $module)
                ->with('success', __('contacts::contacts.custom_field_deleted'));
        } catch (\DomainException $e) {
            return back()->withErrors(['error' => $e->getMessage()]);
        }
    }

    /**
     * Delete multiple custom fields
     */
    public function bulkDestroy(Request $request, string $module)
    {
        $validated = $request->validate([
            'ids' => 'required|array',
            'ids.*' => 'required|integer',
        ]);

        try {
            foreach ($validated['ids'] as $id) {
                $this->deleteCustomFieldUseCase->execute((int) $id);
            }
            $this->moduleRegistry->refresh();

            return response()->json(['success' => true]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 400);
        }
    }

    /**
     * Show form to edit custom field
     */
    public function edit(string $module, int $id)
    {
        $moduleInfo = $this->getModuleInfo($module);
        if (!$moduleInfo) {
            abort(404, "Module $module not found");
        }

        $customField = $this->customFieldRepository->findById($id);
        if (!$customField) {
            abort(404, "Custom field $id not found");
        }


        $fieldTypes = array_map(
            fn($type) => $type,
            CustomFieldType::getCustomFieldTypes()
        );
        $blocks = $this->getModuleBlocks((int) $moduleInfo->tabid);

        return view('contacts_module::custom-fields.edit', [
            'customField' => $customField,
            'fieldTypes' => $fieldTypes,
            'blocks' => $blocks,
            'module' => $module,
            'moduleInfo' => $moduleInfo,
        ]);
    }

    public function update(Request $request, string $module, int $id)
    {
        $moduleInfo = $this->getModuleInfo($module);
        if (!$moduleInfo) {
            abort(404, "Module $module not found");
        }

        $validated = $request->validate([
            'fieldlabel_en' => 'required|string|max:100',
            'fieldlabel_ar' => 'nullable|string|max:100',
            'block' => 'required|integer',
            'quickcreate' => 'boolean',
            'helpinfo' => 'nullable|string',
            'defaultvalue' => 'nullable',
            'length' => 'nullable|integer',
            'decimal_places' => 'nullable|integer',
            'picklist_values' => 'nullable|string',
            'role_based_picklist' => 'nullable|boolean',
            'allow_multiple_files' => 'nullable|boolean',
            'acceptable_file_types' => 'nullable|string',
        ]);

        try {
            $field = $this->customFieldRepository->findById($id);
            if (!$field) {
                abort(404, "Field not found");
            }

            $currentUitype = $field->getUitype();
            $uitypeValue = $currentUitype->value;

            // Handle Picklist type toggle (15 vs 16)
            if (in_array($uitypeValue, [15, 16])) {
                $isRoleBased = $request->input('role_based_picklist', false);
                $uitypeValue = $isRoleBased ? 15 : 16;
                $validated['uitype'] = $uitypeValue;
                $currentUitype = CustomFieldType::from($uitypeValue);
            }

            // Generate typeofdata based on current/new uitype
            $typeofdata = $currentUitype->getTypeOfData();
            $parts = explode('~', $typeofdata);
            if (isset($validated['length'])) {
                $config = $validated['length'];
                if (isset($validated['decimal_places']) && $currentUitype->value == 71) {
                    $config .= ',' . $validated['decimal_places'];
                }
                $parts[2] = $config;
            }
            $validated['typeofdata'] = implode('~', $parts);

            // Handle default value (could be array from multiselect)
            if (isset($validated['defaultvalue'])) {
                if (is_array($validated['defaultvalue'])) {
                    $validated['defaultvalue'] = '|##|' . implode('|##|', $validated['defaultvalue']) . '|##|';
                } elseif (!empty($validated['defaultvalue']) && $uitypeValue == 33) {
                    $validated['defaultvalue'] = '|##|' . $validated['defaultvalue'] . '|##|';
                }
            }

            $dto = \App\Modules\Tenant\Contacts\Application\DTOs\UpdateCustomFieldDTO::fromRequest($id, $validated);

            $this->updateCustomFieldUseCase->execute($dto);
            $this->moduleRegistry->refresh();

            return redirect()
                ->route('tenant.settings.modules.layout', $module)
                ->with('success', __('contacts::contacts.custom_field_updated'));
        } catch (\DomainException $e) {
            return back()
                ->withInput()
                ->withErrors(['error' => $e->getMessage()]);
        }
    }

    /**
     * Get module information from vtiger_tab
     */
    private function getModuleInfo(string $module): ?object
    {
        return \Illuminate\Support\Facades\DB::connection('tenant')
            ->table('vtiger_tab')
            ->where('name', 'like', $module) // Case-insensitive on most DBs
            ->first();
    }

    /**
     * Get available blocks for a module
     */
    private function getModuleBlocks(int $tabId): array
    {
        $blocks = \Illuminate\Support\Facades\DB::connection('tenant')
            ->table('vtiger_blocks')
            ->where('tabid', $tabId)
            ->where('visible', 0) // vtiger 0=visible, 1=invisible
            ->orderBy('sequence')
            ->get();

        return $blocks->map(fn($b) => [
            'id' => $b->blockid,
            'label' => $b->blocklabel
        ])->toArray();
    }
}
