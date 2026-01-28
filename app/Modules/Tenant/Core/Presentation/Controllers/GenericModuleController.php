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

            // 1. Insert into crmentity
            $crmId = DB::connection('tenant')->table('vtiger_crmentity')->insertGetId([
                'smcreatorid' => $userId,
                'smownerid' => $userId,
                'setype' => $moduleName,
                'createdtime' => $now,
                'modifiedtime' => $now,
                'version' => 0,
                'presence' => 0,
                'deleted' => 0
            ]);

            // 2. Group data by table
            $dataByTable = [];
            foreach ($fields as $field) {
                if ($request->has($field->column)) {
                    $dataByTable[$field->table][$field->column] = $request->input($field->column);
                }
            }

            // 3. Insert into base table and other extension tables
            foreach ($dataByTable as $table => $data) {
                if ($table === 'vtiger_crmentity')
                    continue;

                // Find primary key for extension table
                $pk = $metadata->baseTableIndex;
                if (!\Illuminate\Support\Facades\Schema::connection('tenant')->hasColumn($table, $pk)) {
                    $columns = \Illuminate\Support\Facades\Schema::connection('tenant')->getColumnListing($table);
                    $pk = collect($columns)->first(fn($col) => str_contains($col, 'id')) ?: $metadata->baseTableIndex;
                }

                $data[$pk] = $crmId;
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

        return view('tenant::core.modules.form', compact('metadata', 'fields', 'record'));
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
                    $dataByTable[$field->table][$field->column] = $request->input($field->column);
                }
            }

            // 3. Update each table
            foreach ($dataByTable as $table => $data) {
                if ($table === 'vtiger_crmentity')
                    continue;

                $pk = $metadata->baseTableIndex;
                if (!\Illuminate\Support\Facades\Schema::connection('tenant')->hasColumn($table, $pk)) {
                    $columns = \Illuminate\Support\Facades\Schema::connection('tenant')->getColumnListing($table);
                    $pk = collect($columns)->first(fn($col) => str_contains($col, 'id')) ?: $metadata->baseTableIndex;
                }

                DB::connection('tenant')->table($table)
                    ->where($pk, $id)
                    ->update($data);
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
}
