<?php

namespace App\Modules\Tenant\Settings\Presentation\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

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
    public function index(Request $request)
    {
        $selectedModule = $request->input('module');

        // Get modules that have dependencies for the filter dropdown
        $activeModules = DB::table('vtiger_picklist_dependency as pd')
            ->join('vtiger_tab as t', 'pd.tabid', '=', 't.tabid')
            ->select('t.name', 't.tablabel')
            ->distinct()
            ->orderBy('t.tablabel')
            ->get();

        // Get all picklist dependencies
        $query = DB::table('vtiger_picklist_dependency as pd')
            ->join('vtiger_tab as t', 'pd.tabid', '=', 't.tabid')
            ->join('vtiger_field as sf', function ($join) {
                $join->on('pd.tabid', '=', 'sf.tabid')
                    ->on('pd.sourcefield', '=', 'sf.fieldname');
            })
            ->join('vtiger_field as tf', function ($join) {
                $join->on('pd.tabid', '=', 'tf.tabid')
                    ->on('pd.targetfield', '=', 'tf.fieldname');
            })
            ->select(
                'pd.tabid',
                't.name as module_name',
                't.tablabel as module_label',
                'pd.sourcefield',
                'pd.targetfield',
                'sf.fieldlabel as sourcefieldlabel',
                'tf.fieldlabel as targetfieldlabel'
            );

        if ($selectedModule) {
            $query->where('t.name', $selectedModule);
        }

        $dependencies = $query->groupBy('pd.tabid', 'pd.sourcefield', 'pd.targetfield', 't.name', 't.tablabel', 'sf.fieldlabel', 'tf.fieldlabel')
            ->get();

        return view('tenant::settings.picklist_dependency.index', compact('dependencies', 'activeModules', 'selectedModule'));
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
            ->get()
            ->map(function ($field) use ($moduleName) {
                $field->fieldlabel = vtranslate($field->fieldlabel, $moduleName);
                return $field;
            });

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

        // Get field labels
        $sourceFieldData = DB::table('vtiger_field')
            ->where('tabid', $moduleData->tabid)
            ->where('fieldname', $sourceField)
            ->first();

        $targetFieldData = DB::table('vtiger_field')
            ->where('tabid', $moduleData->tabid)
            ->where('fieldname', $targetField)
            ->first();

        $sourceFieldLabel = $sourceFieldData ? $sourceFieldData->fieldlabel : $sourceField;
        $targetFieldLabel = $targetFieldData ? $targetFieldData->fieldlabel : $targetField;

        // Get source field values
        $sourceTableName = 'vtiger_' . $sourceField;
        $sourceValues = DB::table($sourceTableName)
            ->where('presence', 1)
            ->orderBy(Schema::hasColumn($sourceTableName, 'sortorderid') ? 'sortorderid' : (Schema::hasColumn($sourceTableName, 'sortid') ? 'sortid' : $sourceField))
            ->get();

        // Get target field values
        $targetTableName = 'vtiger_' . $targetField;
        $targetValues = DB::table($targetTableName)
            ->where('presence', 1)
            ->orderBy(Schema::hasColumn($targetTableName, 'sortorderid') ? 'sortorderid' : (Schema::hasColumn($targetTableName, 'sortid') ? 'sortid' : $targetField))
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
            'sourceFieldLabel',
            'targetFieldLabel',
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
