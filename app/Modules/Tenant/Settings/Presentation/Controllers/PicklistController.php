<?php

namespace App\Modules\Tenant\Settings\Presentation\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * PicklistController
 * 
 * Manages picklist (dropdown) field values across all CRM modules:
 * - Add/Edit/Delete picklist values
 * - Role-based picklist value assignment
 * - Drag-and-drop reordering
 */
class PicklistController extends Controller
{
    /**
     * Display picklist management interface
     */
    public function index()
    {
        // Get all modules that have picklist fields (uitype 15, 16, 33)
        $modules = $this->getPicklistSupportedModules();

        return view('tenant::settings.picklist.index', compact('modules'));
    }

    /**
     * Get picklist fields for a specific module
     */
    public function getPicklistFields(Request $request)
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
            ->whereIn('uitype', [15, 16, 33, 55]) // Picklist types
            ->where('presence', '!=', 1) // Not deleted
            ->select('fieldid', 'fieldname', 'fieldlabel', 'uitype')
            ->get();

        return response()->json(['fields' => $fields]);
    }

    /**
     * Get picklist values for a specific field
     */
    public function getPicklistValues(Request $request)
    {
        $fieldName = $request->input('fieldname');

        // Get picklist values from dynamic table
        $tableName = 'vtiger_' . $fieldName;

        try {
            $sortColumn = Schema::hasColumn($tableName, 'sortorderid') ? 'sortorderid' : (Schema::hasColumn($tableName, 'sortid') ? 'sortid' : null);

            $query = DB::table($tableName);
            if ($sortColumn) {
                $query->orderBy($sortColumn);
            }
            $values = $query->get();

            return response()->json(['values' => $values]);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Failed to fetch picklist values'], 500);
        }
    }

    /**
     * Add a new picklist value
     */
    public function addValue(Request $request)
    {
        $request->validate([
            'fieldname' => 'required|string',
            'value' => 'required|string',
        ]);

        $fieldName = $request->input('fieldname');
        $value = $request->input('value');

        $tableName = 'vtiger_' . $fieldName;

        try {
            $sortColumn = Schema::hasColumn($tableName, 'sortorderid') ? 'sortorderid' : (Schema::hasColumn($tableName, 'sortid') ? 'sortid' : null);

            // Get max sequence
            $maxSequence = $sortColumn ? (DB::table($tableName)->max($sortColumn) ?? 0) : 0;
            $maxpicklist_valueid = DB::table($tableName)->max('picklist_valueid') ?? 0;

            // Insert new value
            $insertData = [
                $fieldName => $value,
                'picklist_valueid' => $maxpicklist_valueid + 1,
                'presence' => 1,
            ];

            if ($sortColumn) {
                $insertData[$sortColumn] = $maxSequence + 1;
            }

            DB::table($tableName)->insert($insertData);

            return response()->json(['success' => true, 'message' => 'Picklist value added successfully']);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Failed to add picklist value: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Update a picklist value
     */
    public function updateValue(Request $request)
    {
        $request->validate([
            'fieldname' => 'required|string',
            'old_value' => 'required|string',
            'new_value' => 'required|string',
        ]);

        $fieldName = $request->input('fieldname');
        $oldValue = $request->input('old_value');
        $newValue = $request->input('new_value');

        $tableName = 'vtiger_' . $fieldName;

        try {
            DB::table($tableName)
                ->where($fieldName, $oldValue)
                ->update([
                    $fieldName => $newValue,
                ]);

            return response()->json(['success' => true, 'message' => 'Picklist value updated successfully']);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Failed to update picklist value'], 500);
        }
    }

    /**
     * Delete a picklist value
     */
    public function deleteValue(Request $request)
    {
        $request->validate([
            'fieldname' => 'required|string',
            'value' => 'required|string',
        ]);

        $fieldName = $request->input('fieldname');
        $value = $request->input('value');

        $tableName = 'vtiger_' . $fieldName;

        try {
            // Mark as deleted (presence = 0)
            DB::table($tableName)
                ->where($fieldName, $value)
                ->delete();

            return response()->json(['success' => true, 'message' => 'Picklist value deleted successfully']);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Failed to delete picklist value'], 500);
        }
    }

    /**
     * Update picklist values order
     */
    public function updateOrder(Request $request)
    {
        $request->validate([
            'fieldname' => 'required|string',
            'order' => 'required|array',
        ]);

        $fieldName = $request->input('fieldname');
        $order = $request->input('order');

        $tableName = 'vtiger_' . $fieldName;

        try {
            $sortColumn = Schema::hasColumn($tableName, 'sortorderid') ? 'sortorderid' : (Schema::hasColumn($tableName, 'sortid') ? 'sortid' : null);

            if (!$sortColumn) {
                return response()->json(['error' => 'No sort column found for this table'], 400);
            }

            foreach ($order as $index => $value) {
                DB::table($tableName)
                    ->where($fieldName, $value)
                    ->update([$sortColumn => $index + 1]);
            }

            return response()->json(['success' => true, 'message' => 'Order updated successfully']);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Failed to update order'], 500);
        }
    }

    /**
     * Get modules that support picklists
     */
    private function getPicklistSupportedModules()
    {
        // Exclude Users and Emails modules
        $excludedModules = ['Users', 'Emails'];

        $modules = DB::table('vtiger_tab')
            ->join('vtiger_field', 'vtiger_tab.tabid', '=', 'vtiger_field.tabid')
            ->whereIn('vtiger_field.uitype', [15, 16, 33])
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
