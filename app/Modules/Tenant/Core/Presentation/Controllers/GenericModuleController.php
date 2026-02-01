<?php

namespace App\Modules\Tenant\Core\Presentation\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Tenant\Core\Domain\Services\ModuleRegistry;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\Facades\DataTables;

class GenericModuleController extends Controller
{
    public function __construct(
        protected ModuleRegistry $registry
    ) {
    }

    public function index(string $moduleName, Request $request)
    {
        $module = $this->registry->get($moduleName);
        if (!$module || $module->metadata->presence != 0) {
            abort(404, "Module $moduleName not found or inactive.");
        }

        $metadata = $module->metadata;
        $fields = ($module->fields)();

        if ($request->ajax()) {
            $query = DB::connection('tenant')->table($metadata->baseTable);

            // Join with crmentity for common fields
            $query->join('vtiger_crmentity', "{$metadata->baseTable}.{$metadata->baseTableIndex}", "=", "vtiger_crmentity.crmid")
                ->where('vtiger_crmentity.deleted', 0);

            // Join with vtiger_users to get the owner name
            $query->leftJoin('vtiger_users', 'vtiger_crmentity.smownerid', '=', 'vtiger_users.id');

            // Identify all required tables from field metadata
            $tables = collect($fields)->pluck('table')->unique()->filter(function ($tbl) use ($metadata) {
                return $tbl !== 'vtiger_crmentity' && $tbl !== $metadata->baseTable && $tbl !== 'vtiger_users' && !empty($tbl);
            });

            $selects = [
                "{$metadata->baseTable}.*",
                "vtiger_crmentity.*",
                "vtiger_users.first_name as owner_firstname",
                "vtiger_users.last_name as owner_lastname"
            ];

            foreach ($tables as $table) {
                if (\Illuminate\Support\Facades\Schema::connection('tenant')->hasTable($table)) {
                    // Find the join column. vtiger is inconsistent (e.g., contactid vs contactaddressid)
                    $joinColumn = $metadata->baseTableIndex;
                    if (!\Illuminate\Support\Facades\Schema::connection('tenant')->hasColumn($table, $joinColumn)) {
                        $columns = \Illuminate\Support\Facades\Schema::connection('tenant')->getColumnListing($table);

                        // Try to find a column that matches the index name or ends with it (e.g. activityid or activity_id)
                        $candidate = collect($columns)->first(function ($col) use ($metadata) {
                            $colLower = strtolower($col);
                            $idxLower = strtolower($metadata->baseTableIndex);
                            return $colLower === $idxLower ||
                                str_ends_with($colLower, '_' . $idxLower) ||
                                str_ends_with($colLower, $idxLower);
                        });

                        // Fallback: any column ending in 'id'
                        if (!$candidate) {
                            $candidate = collect($columns)->first(fn($col) => str_ends_with(strtolower($col), 'id'));
                        }

                        if ($candidate)
                            $joinColumn = $candidate;
                    }

                    $query->leftJoin($table, "{$metadata->baseTable}.{$metadata->baseTableIndex}", "=", "{$table}.{$joinColumn}");
                    $selects[] = "{$table}.*";
                }
            }

            $query->select($selects);

            return DataTables::query($query)
                ->editColumn('smownerid', function ($row) {
                    $name = trim(($row->owner_firstname ?? '') . ' ' . ($row->owner_lastname ?? ''));
                    return $name ?: __('tenant::tenant.system');
                })
                ->editColumn('createdtime', fn($row) => \Carbon\Carbon::parse($row->createdtime)->format('Y-m-d H:i'))
                ->editColumn('modifiedtime', fn($row) => \Carbon\Carbon::parse($row->modifiedtime)->format('Y-m-d H:i'))
                ->escapeColumns([])
                ->make(true);
        }

        return view('tenant::core.modules.index', compact('metadata', 'fields'));
    }

    public function create(string $moduleName)
    {
        $module = $this->registry->get($moduleName);
        if (!$module || $module->metadata->presence != 0) {
            abort(404);
        }

        $metadata = $module->metadata;
        $fields = ($module->fields)();

        return view('tenant::core.modules.form', compact('metadata', 'fields'));
    }

    public function store(string $moduleName, Request $request)
    {
        $module = $this->registry->get($moduleName);
        if (!$module || $module->metadata->presence != 0) {
            abort(404);
        }

        $metadata = $module->metadata;
        $fields = collect(($module->fields)());

        // Basic validation for mandatory fields
        $rules = [];
        foreach ($fields as $field) {
            if ($field->isMandatory && $field->presence == 0) {
                $rules[$field->column] = 'required';
            }
        }
        $request->validate($rules);

        return DB::connection('tenant')->transaction(function () use ($request, $metadata, $fields, $moduleName) {
            $now = now()->format('Y-m-d H:i:s');
            $userId = auth('tenant')->id();

            // 1. Get next CRM ID
            $crmId = vtiger_next_id('tenant');

            // 2. Insert into crmentity
            DB::connection('tenant')->table('vtiger_crmentity')->insert([
                'crmid' => $crmId,
                'smcreatorid' => $userId,
                'smownerid' => $userId,
                'setype' => $moduleName,
                'createdtime' => $now,
                'modifiedtime' => $now,
                'version' => 0,
                'presence' => 0,
                'deleted' => 0,
                'label' => $this->generateLabel($request, $fields)
            ]);

            // 2. Group data by table
            $dataByTable = [];
            foreach ($fields as $field) {
                if ($request->has($field->column)) {
                    $val = $request->input($field->column);
                    // Handle arrays (multi-select picklists, multi-checkboxes, etc.)
                    if (is_array($val)) {
                        if ($field->uiType == 33) {
                            $val = implode(' |##| ', $val);
                        } else {
                            $val = json_encode($val);
                        }
                    }
                    $dataByTable[$field->table][$field->column] = $val;
                }

                // Handle Auto-generated sequence numbering (uitype 4)
                if ($field->uiType == 4 && empty($dataByTable[$field->table][$field->column])) {
                    $nextNo = vtiger_next_no($metadata->name, 'tenant');
                    if ($nextNo) {
                        $dataByTable[$field->table][$field->column] = $nextNo;
                    }
                }
            }

            // Handle file uploads
            foreach ($request->allFiles() as $col => $file) {
                $field = $fields->firstWhere('column', $col);
                if ($field) {
                    if (is_array($file)) {
                        $paths = [];
                        foreach ($file as $singleFile) {
                            $paths[] = $singleFile->store('modules/' . $moduleName, 'tenancy');
                        }
                        $dataByTable[$field->table][$col] = json_encode($paths);
                    } else {
                        $path = $file->store('modules/' . $moduleName, 'tenancy');
                        $dataByTable[$field->table][$col] = $path;
                    }
                }
            }

            // 3. Update crmentity with form data if present (e.g. smownerid)
            $crmentityData = [
                'smownerid' => $userId, // Default
            ];
            if (isset($dataByTable['vtiger_crmentity'])) {
                foreach ($dataByTable['vtiger_crmentity'] as $col => $val) {
                    $crmentityData[$col] = $val;
                }
                // Map assigned_user_id to smownerid if it came from a field named so
                if (isset($dataByTable['vtiger_crmentity']['assigned_user_id'])) {
                    $crmentityData['smownerid'] = $dataByTable['vtiger_crmentity']['assigned_user_id'];
                }
            }

            DB::connection('tenant')->table('vtiger_crmentity')
                ->where('crmid', $crmId)
                ->update($crmentityData);

            // 4. Insert into base table and other extension tables
            foreach ($dataByTable as $table => $data) {
                if ($table === 'vtiger_crmentity')
                    continue;

                // Find primary key for extension table
                $pk = $metadata->baseTableIndex;
                if (!\Illuminate\Support\Facades\Schema::connection('tenant')->hasTable($table))
                    continue;

                if (!\Illuminate\Support\Facades\Schema::connection('tenant')->hasColumn($table, $pk)) {
                    $columns = \Illuminate\Support\Facades\Schema::connection('tenant')->getColumnListing($table);
                    $pk = collect($columns)->first(fn($col) => str_contains(strtolower($col), 'id')) ?: $metadata->baseTableIndex;
                }

                $data[$pk] = $crmId;

                // SPECIAL CASE: vtiger_crmentity_user_field needs userid
                if ($table === 'vtiger_crmentity_user_field' && !isset($data['userid'])) {
                    $data['userid'] = $userId;
                }

                DB::connection('tenant')->table($table)->insert($data);
            }

            return redirect()->route('tenant.modules.show', [$moduleName, $crmId])
                ->with('success', __('tenant::tenant.created_successfully'));
        });
    }

    public function edit(string $moduleName, int $id)
    {
        $module = $this->registry->get($moduleName);
        if (!$module || $module->metadata->presence != 0) {
            abort(404);
        }

        $metadata = $module->metadata;
        $fields = ($module->fields)();

        $record = $this->getRecordById($metadata, $fields, $id);
        if (!$record)
            abort(404);

        // Resolve reference field labels
        foreach ($fields as $field) {
            $uitype = (int) $field->uiType;
            if (in_array($uitype, [10, 51, 52, 57, 58, 59, 66, 68, 73, 75, 76, 78, 80, 81])) {
                $val = $record->{$field->column} ?? null;
                if ($val) {
                    $record->{$field->column . '_label'} = $this->getEntityLabel($val);
                }
            }
        }

        return view('tenant::core.modules.form', compact('metadata', 'fields', 'record'));
    }

    protected function getEntityLabel(int $id): ?string
    {
        $entity = DB::connection('tenant')->table('vtiger_crmentity')
            ->where('crmid', $id)
            ->first();

        if (!$entity)
            return null;

        return $entity->label ?? "Record #$id";
    }

    public function update(string $moduleName, int $id, Request $request)
    {
        $module = $this->registry->get($moduleName);
        if (!$module || $module->metadata->presence != 0) {
            abort(404);
        }

        $metadata = $module->metadata;
        $fields = collect(($module->fields)());

        return DB::connection('tenant')->transaction(function () use ($request, $metadata, $fields, $moduleName, $id) {
            $now = now()->format('Y-m-d H:i:s');

            // 1. Update crmentity
            DB::connection('tenant')->table('vtiger_crmentity')
                ->where('crmid', $id)
                ->update([
                    'modifiedtime' => $now,
                    'modifiedby' => auth('tenant')->id()
                ]);

            // 2. Group data by table
            $dataByTable = [];
            foreach ($fields as $field) {
                if ($request->has($field->column)) {
                    $val = $request->input($field->column);
                    // Handle arrays (multi-select picklists, multi-checkboxes, etc.)
                    if (is_array($val)) {
                        if ($field->uiType == 33) {
                            $val = implode(' |##| ', $val);
                        } else {
                            $val = json_encode($val);
                        }
                    }
                    $dataByTable[$field->table][$field->column] = $val;
                }
            }

            // Handle file uploads
            foreach ($request->allFiles() as $col => $file) {
                $field = $fields->firstWhere('column', $col);
                if ($field) {
                    if (is_array($file)) {
                        $paths = [];
                        foreach ($file as $singleFile) {
                            $paths[] = $singleFile->store('modules/' . $moduleName, 'tenancy');
                        }
                        $dataByTable[$field->table][$col] = json_encode($paths);
                    } else {
                        $path = $file->store('modules/' . $moduleName, 'tenancy');
                        $dataByTable[$field->table][$col] = $path;
                    }
                }
            }

            // 3. Update crmentity
            $crmentityData = [
                'modifiedtime' => $now,
                'modifiedby' => auth('tenant')->id(),
                'label' => $this->generateLabel($request, $fields)
            ];

            if (isset($dataByTable['vtiger_crmentity'])) {
                foreach ($dataByTable['vtiger_crmentity'] as $col => $val) {
                    $crmentityData[$col] = $val;
                }
                // Map assigned_user_id to smownerid
                if (isset($dataByTable['vtiger_crmentity']['assigned_user_id'])) {
                    $crmentityData['smownerid'] = $dataByTable['vtiger_crmentity']['assigned_user_id'];
                }
            }

            DB::connection('tenant')->table('vtiger_crmentity')
                ->where('crmid', $id)
                ->update($crmentityData);

            // 4. Update each module table
            foreach ($dataByTable as $table => $data) {
                if ($table === 'vtiger_crmentity')
                    continue;

                if (!\Illuminate\Support\Facades\Schema::connection('tenant')->hasTable($table))
                    continue;

                $pk = $metadata->baseTableIndex;
                if (!\Illuminate\Support\Facades\Schema::connection('tenant')->hasColumn($table, $pk)) {
                    $columns = \Illuminate\Support\Facades\Schema::connection('tenant')->getColumnListing($table);
                    $pk = collect($columns)->first(fn($col) => str_contains(strtolower($col), 'id')) ?: $metadata->baseTableIndex;
                }

                if ($table === 'vtiger_crmentity_user_field') {
                    DB::connection('tenant')->table($table)
                        ->updateOrInsert(
                            ['recordid' => $id, 'userid' => auth('tenant')->id()],
                            $data
                        );
                } else {
                    DB::connection('tenant')->table($table)
                        ->where($pk, $id)
                        ->update($data);
                }
            }

            return redirect()->route('tenant.modules.show', [$moduleName, $id])
                ->with('success', __('tenant::tenant.updated_successfully'));
        });
    }

    public function show(string $moduleName, int $id)
    {
        $module = $this->registry->get($moduleName);
        if (!$module || $module->metadata->presence != 0) {
            abort(404);
        }

        $metadata = $module->metadata;
        $fields = ($module->fields)();

        $record = $this->getRecordById($metadata, $fields, $id);
        if (!$record)
            abort(404);

        return view('tenant::core.modules.show', compact('metadata', 'fields', 'record'));
    }

    public function destroy(string $moduleName, int $id)
    {
        // soft delete in vtiger means setting deleted=1 in crmentity
        DB::connection('tenant')->table('vtiger_crmentity')
            ->where('crmid', $id)
            ->update(['deleted' => 1]);

        return redirect()->route('tenant.modules.index', $moduleName)
            ->with('success', __('tenant::tenant.deleted_successfully'));
    }

    protected function getRecordById($metadata, $fields, $id)
    {
        $query = DB::connection('tenant')->table($metadata->baseTable)
            ->join('vtiger_crmentity', "{$metadata->baseTable}.{$metadata->baseTableIndex}", "=", "vtiger_crmentity.crmid");

        $tables = collect($fields)->pluck('table')->unique()->filter(function ($tbl) use ($metadata) {
            return $tbl !== 'vtiger_crmentity' && $tbl !== $metadata->baseTable && !empty($tbl);
        });

        $selects = ["{$metadata->baseTable}.*", "vtiger_crmentity.*"];

        foreach ($tables as $table) {
            if (\Illuminate\Support\Facades\Schema::connection('tenant')->hasTable($table)) {
                $joinColumn = $metadata->baseTableIndex;
                if (!\Illuminate\Support\Facades\Schema::connection('tenant')->hasColumn($table, $joinColumn)) {
                    $columns = \Illuminate\Support\Facades\Schema::connection('tenant')->getColumnListing($table);

                    $candidate = collect($columns)->first(function ($col) use ($metadata) {
                        $colLower = strtolower($col);
                        $idxLower = strtolower($metadata->baseTableIndex);
                        return $colLower === $idxLower ||
                            str_ends_with($colLower, '_' . $idxLower) ||
                            str_ends_with($colLower, $idxLower);
                    });

                    if (!$candidate) {
                        $candidate = collect($columns)->first(fn($col) => str_ends_with(strtolower($col), 'id'));
                    }

                    if ($candidate)
                        $joinColumn = $candidate;
                }
                $query->leftJoin($table, "{$metadata->baseTable}.{$metadata->baseTableIndex}", "=", "{$table}.{$joinColumn}");
                $selects[] = "{$table}.*";
            }
        }

        return $query->select($selects)
            ->where("{$metadata->baseTable}.{$metadata->baseTableIndex}", $id)
            ->first();
    }
    protected function generateLabel(Request $request, $fields): string
    {
        $moduleName = $request->route('moduleName');
        $entityInfo = DB::connection('tenant')->table('vtiger_entityname')
            ->where('modulename', $moduleName)
            ->first();

        if ($entityInfo) {
            $labelFields = explode(',', $entityInfo->fieldname);
            $labels = [];
            foreach ($labelFields as $f) {
                if ($request->has($f)) {
                    $labels[] = $request->input($f);
                }
            }
            if (!empty($labels)) {
                return implode(' ', $labels);
            }
        }

        // Fallback for modules like EmailTemplates not in entityname
        if ($moduleName === 'EmailTemplates') {
            return $request->input('templatename', 'New Email Template');
        }

        // Final fallback: use first non-empty value from request
        foreach ($request->all() as $key => $value) {
            if ($key !== '_token' && is_string($value) && !empty($value)) {
                return $value;
            }
        }

        return $moduleName . ' Record';
    }
    public function referenceSearch(string $moduleName, string $fieldName, Request $request)
    {
        $q = $request->input('q');

        // 1. Find related modules for this field
        $relModules = DB::connection('tenant')->table('vtiger_fieldmodulerel')
            ->join('vtiger_field', 'vtiger_fieldmodulerel.fieldid', '=', 'vtiger_field.fieldid')
            ->join('vtiger_tab', 'vtiger_field.tabid', '=', 'vtiger_tab.tabid')
            ->where('vtiger_tab.name', $moduleName)
            ->where('vtiger_field.fieldname', $fieldName)
            ->pluck('relmodule')
            ->toArray();

        // Standard fallbacks for common Vtiger uitypes if not in rel table
        // 51: Account, 52: Contact, 73: Account (HelpDesk), 81: Vendor (Product)
        if (empty($relModules)) {
            $fallbacks = [
                'accountid' => ['Accounts'],
                'account_id' => ['Accounts'],
                'contactid' => ['Contacts'],
                'contact_id' => ['Contacts'],
                'parent_id' => ['Accounts', 'Contacts', 'Leads'],
                'productid' => ['Products'],
                'product_id' => ['Products'],
                'vendorid' => ['Vendors'],
                'vendor_id' => ['Vendors'],
            ];
            if (isset($fallbacks[$fieldName])) {
                $relModules = $fallbacks[$fieldName];
            }
        }

        $results = [];
        foreach ($relModules as $relModule) {
            $entityInfo = DB::connection('tenant')->table('vtiger_entityname')
                ->where('modulename', $relModule)
                ->first();

            if (!$entityInfo)
                continue;

            $labelFields = explode(',', $entityInfo->fieldname);
            $query = DB::connection('tenant')->table($entityInfo->tablename);

            // Join crmentity to respect soft deletes
            $query->join('vtiger_crmentity', "{$entityInfo->tablename}.{$entityInfo->entityidfield}", '=', 'vtiger_crmentity.crmid')
                ->where('vtiger_crmentity.deleted', 0);

            $query->where(function ($sub) use ($labelFields, $q) {
                foreach ($labelFields as $f) {
                    $sub->orWhere($f, 'like', "%$q%");
                }
            });

            $items = $query->limit(15)->get();
            foreach ($items as $item) {
                $labels = [];
                foreach ($labelFields as $f) {
                    $labels[] = $item->{$f};
                }
                $results[] = [
                    'id' => $item->{$entityInfo->entityidfield},
                    'text' => '[' . vtranslate($relModule, 'Vtiger') . '] ' . implode(' ', $labels)
                ];
            }
        }

        return response()->json([
            'items' => $results,
            'total_count' => count($results)
        ]);
    }
}
