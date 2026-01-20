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

        $fieldTypes = CustomFieldType::cases();

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

    /**
     * Store new custom field
     */
    public function store(Request $request, string $module)
    {
        $moduleInfo = $this->getModuleInfo($module);
        if (!$moduleInfo) {
            abort(404, "Module $module not found");
        }

        $validated = $request->validate([
            'fieldname' => 'required|string|max:50|regex:/^[a-zA-Z0-9_]+$/',
            'fieldlabel' => 'required|string|max:100', // Acts as translation key
            'uitype' => 'required|integer',
            'block' => 'required|integer',
            'typeofdata' => 'string|max:100',
            'quickcreate' => 'boolean',
            'helpinfo' => 'nullable|string',
            'defaultvalue' => 'nullable|string',
            'picklist_values' => 'nullable|string', // Comma or newline separated values
        ]);

        try {
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

        $fieldTypes = CustomFieldType::cases();
        $blocks = $this->getModuleBlocks((int) $moduleInfo->tabid);

        return view('contacts_module::custom-fields.edit', [
            'customField' => $customField,
            'fieldTypes' => $fieldTypes,
            'blocks' => $blocks,
            'module' => $module,
            'moduleInfo' => $moduleInfo,
        ]);
    }

    /**
     * Update custom field
     */
    public function update(Request $request, string $module, int $id)
    {
        $moduleInfo = $this->getModuleInfo($module);
        if (!$moduleInfo) {
            abort(404, "Module $module not found");
        }

        $validated = $request->validate([
            'fieldlabel' => 'required|string|max:100',
            'block' => 'required|integer',
            'typeofdata' => 'string|max:100',
            'quickcreate' => 'boolean',
            'helpinfo' => 'nullable|string',
            'defaultvalue' => 'nullable|string',
        ]);

        try {
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
