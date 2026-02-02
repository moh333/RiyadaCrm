<?php

namespace App\Modules\Tenant\Settings\Presentation\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * PicklistDependencyController
 * 
 * Manages picklist dependencies where target field values depend on source field selections:
 * - Create/Edit/Delete dependencies
 * - Value-level dependency configuration
 * - Cyclic dependency prevention
 * - Visual dependency graph
 */
class PicklistDependencyController extends Controller
{
    /**
     * Display picklist dependency list
     */
    public function index()
    {
        // Get all picklist dependencies
        $dependencies = DB::table('vtiger_picklist_dependency as pd')
            ->join('vtiger_tab as t', 'pd.tabid', '=', 't.tabid')
            ->select(
                'pd.id',
                'pd.tabid',
                't.name as module_name',
                't.tablabel as module_label',
                'pd.sourcefield',
                'pd.targetfield'
            )
            ->groupBy('pd.tabid', 'pd.sourcefield', 'pd.targetfield', 'pd.id', 't.name', 't.tablabel')
            ->get();

        return view('tenant::settings.picklist_dependency.index', compact('dependencies'));
    }

    /**
     * Show form to create a new dependency
     */
    public function create()
    {
        // Get all modules that have picklist fields
        $modules = $this->getPicklistSupportedModules();

        return view('tenant::settings.picklist_dependency.create', compact('modules'));
    }

    /**
     * Get available picklist fields for a module
     */
    public function getAvailablePicklists(Request $request)
    {
        $moduleName = $request->input('module');

        // Get module ID
        $module = DB::table('vtiger_tab')
            ->where('name', $moduleName)
            ->first();

        if (!$module) {
            return response()->json(['error' => 'Module not found'], 404);
        }

        // Get picklist fields for this module
        $fields = DB::table('vtiger_field')
            ->where('tabid', $module->tabid)
            ->whereIn('uitype', [15, 16]) // Only standard picklists, not multi-select
            ->where('presence', '!=', 1)
            ->select('fieldid', 'fieldname', 'fieldlabel', 'uitype')
            ->get();

        return response()->json(['fields' => $fields]);
    }

    /**
     * Show dependency editor
     */
    public function edit(Request $request)
    {
        $module = $request->input('module');
        $sourceField = $request->input('source_field');
        $targetField = $request->input('target_field');

        // Get module ID
        $moduleData = DB::table('vtiger_tab')
            ->where('name', $module)
            ->first();

        if (!$moduleData) {
            return redirect()->back()->with('error', 'Module not found');
        }

        // Get source field values
        $sourceTableName = 'vtiger_' . $sourceField;
        $sourceValues = DB::table($sourceTableName)
            ->where('presence', 1)
            ->orderBy('sortorderid')
            ->get();

        // Get target field values
        $targetTableName = 'vtiger_' . $targetField;
        $targetValues = DB::table($targetTableName)
            ->where('presence', 1)
            ->orderBy('sortorderid')
            ->get();

        // Get existing dependency mappings
        $existingMappings = DB::table('vtiger_picklist_dependency')
            ->where('tabid', $moduleData->tabid)
            ->where('sourcefield', $sourceField)
            ->where('targetfield', $targetField)
            ->get()
            ->keyBy('sourcevalue');

        return view('tenant::settings.picklist_dependency.edit', compact(
            'module',
            'sourceField',
            'targetField',
            'sourceValues',
            'targetValues',
            'existingMappings'
        ));
    }

    /**
     * Save picklist dependency
     */
    public function store(Request $request)
    {
        $request->validate([
            'module' => 'required|string',
            'source_field' => 'required|string',
            'target_field' => 'required|string',
            'mappings' => 'required|array',
        ]);

        $module = $request->input('module');
        $sourceField = $request->input('source_field');
        $targetField = $request->input('target_field');
        $mappings = $request->input('mappings');

        // Get module ID
        $moduleData = DB::table('vtiger_tab')
            ->where('name', $module)
            ->first();

        if (!$moduleData) {
            return response()->json(['error' => 'Module not found'], 404);
        }

        // Check for cyclic dependency
        if ($this->checkCyclicDependency($moduleData->tabid, $sourceField, $targetField)) {
            return response()->json(['error' => 'Cyclic dependency detected'], 400);
        }

        try {
            DB::beginTransaction();

            // Delete existing dependencies for this combination
            DB::table('vtiger_picklist_dependency')
                ->where('tabid', $moduleData->tabid)
                ->where('sourcefield', $sourceField)
                ->where('targetfield', $targetField)
                ->delete();

            // Insert new dependencies
            foreach ($mappings as $sourceValue => $targetValues) {
                if (!empty($targetValues)) {
                    DB::table('vtiger_picklist_dependency')->insert([
                        'tabid' => $moduleData->tabid,
                        'sourcefield' => $sourceField,
                        'targetfield' => $targetField,
                        'sourcevalue' => $sourceValue,
                        'targetvalues' => json_encode($targetValues),
                    ]);
                }
            }

            DB::commit();

            return response()->json(['success' => true, 'message' => 'Dependency saved successfully']);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['error' => 'Failed to save dependency: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Delete a picklist dependency
     */
    public function destroy(Request $request)
    {
        $request->validate([
            'module' => 'required|string',
            'source_field' => 'required|string',
            'target_field' => 'required|string',
        ]);

        $module = $request->input('module');
        $sourceField = $request->input('source_field');
        $targetField = $request->input('target_field');

        // Get module ID
        $moduleData = DB::table('vtiger_tab')
            ->where('name', $module)
            ->first();

        if (!$moduleData) {
            return response()->json(['error' => 'Module not found'], 404);
        }

        try {
            DB::table('vtiger_picklist_dependency')
                ->where('tabid', $moduleData->tabid)
                ->where('sourcefield', $sourceField)
                ->where('targetfield', $targetField)
                ->delete();

            return response()->json(['success' => true, 'message' => 'Dependency deleted successfully']);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Failed to delete dependency'], 500);
        }
    }

    /**
     * Check for cyclic dependencies
     */
    private function checkCyclicDependency($tabid, $sourceField, $targetField)
    {
        // Check if another parent field exists for the same target field
        $existingParent = DB::table('vtiger_picklist_dependency')
            ->where('tabid', $tabid)
            ->where('targetfield', $targetField)
            ->where('sourcefield', '!=', $sourceField)
            ->exists();

        return $existingParent;
    }

    /**
     * Get modules that support picklists
     */
    private function getPicklistSupportedModules()
    {
        $excludedModules = ['Users', 'Emails'];

        $modules = DB::table('vtiger_tab')
            ->join('vtiger_field', 'vtiger_tab.tabid', '=', 'vtiger_field.tabid')
            ->whereIn('vtiger_field.uitype', [15, 16])
            ->whereNotIn('vtiger_tab.name', $excludedModules)
            ->where('vtiger_tab.presence', '!=', 1)
            ->where('vtiger_field.presence', '!=', 1)
            ->select('vtiger_tab.tabid', 'vtiger_tab.name', 'vtiger_tab.tablabel')
            ->distinct()
            ->orderBy('vtiger_tab.tabid')
            ->get();

        return $modules;
    }
}
