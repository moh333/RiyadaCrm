<?php

use App\Modules\Core\VtigerModules\Contracts\ModuleRegistryInterface;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Test Routes for Vtiger Module Management Engine
|--------------------------------------------------------------------------
|
| These routes are for testing the module registry functionality.
| Remove or protect these routes in production.
|
*/

Route::middleware(['web', 'auth:tenant'])->prefix('test/modules')->group(function () {

    // List all modules
    Route::get('/', function (ModuleRegistryInterface $registry) {
        $modules = $registry->all();

        return response()->json([
            'total' => $modules->count(),
            'modules' => $modules->map(fn($m) => [
                'id' => $m->getId(),
                'name' => $m->getName(),
                'label' => $m->getLabel(),
                'is_active' => $m->isActive(),
                'is_custom' => $m->isCustom(),
                'fields_count' => $m->fields()->count(),
                'blocks_count' => $m->blocks()->count(),
                'relations_count' => $m->relations()->count(),
            ])->values(),
        ]);
    });

    // Get specific module details
    Route::get('/{module}', function (string $module, ModuleRegistryInterface $registry) {
        try {
            $moduleDefinition = $registry->get($module);

            return response()->json([
                'module' => [
                    'id' => $moduleDefinition->getId(),
                    'name' => $moduleDefinition->getName(),
                    'label' => $moduleDefinition->getLabel(),
                    'base_table' => $moduleDefinition->getBaseTable(),
                    'base_index' => $moduleDefinition->getBaseIndex(),
                    'is_entity' => $moduleDefinition->isEntity(),
                    'is_custom' => $moduleDefinition->isCustom(),
                    'is_active' => $moduleDefinition->isActive(),
                ],
                'fields' => $moduleDefinition->fields()->map(fn($f) => [
                    'name' => $f->getFieldName(),
                    'label' => $f->getLabel(),
                    'column' => $f->getColumnName(),
                    'table' => $f->getTableName(),
                    'uitype' => $f->getUitype(),
                    'type' => $f->getFieldType(),
                    'is_mandatory' => $f->isMandatory(),
                    'is_custom' => $f->isCustomField(),
                    'is_editable' => $f->isEditable(),
                ])->values(),
                'blocks' => $moduleDefinition->blocks()->map(fn($b) => [
                    'id' => $b->getId(),
                    'label' => $b->getLabel(),
                    'sequence' => $b->getSequence(),
                    'is_visible' => $b->isVisible(),
                ])->values(),
                'relations' => $moduleDefinition->relations()->map(fn($r) => [
                    'target' => $r->getTargetModule(),
                    'type' => $r->getRelationType(),
                    'field' => $r->getRelatedField(),
                    'label' => $r->getLabel(),
                ])->values(),
            ]);
        } catch (\InvalidArgumentException $e) {
            return response()->json(['error' => $e->getMessage()], 404);
        }
    });

    // Get only custom fields for a module
    Route::get('/{module}/custom-fields', function (string $module, ModuleRegistryInterface $registry) {
        try {
            $moduleDefinition = $registry->get($module);
            $customFields = $moduleDefinition->getCustomFields();

            return response()->json([
                'module' => $module,
                'custom_fields_count' => $customFields->count(),
                'custom_fields' => $customFields->map(fn($f) => [
                    'name' => $f->getFieldName(),
                    'label' => $f->getLabel(),
                    'column' => $f->getColumnName(),
                    'uitype' => $f->getUitype(),
                    'type' => $f->getFieldType(),
                    'is_mandatory' => $f->isMandatory(),
                ])->values(),
            ]);
        } catch (\InvalidArgumentException $e) {
            return response()->json(['error' => $e->getMessage()], 404);
        }
    });

    // Refresh cache
    Route::post('/refresh', function (ModuleRegistryInterface $registry) {
        $registry->refresh();

        return response()->json([
            'message' => 'Module registry cache refreshed successfully',
            'modules_count' => $registry->all()->count(),
        ]);
    });
});
