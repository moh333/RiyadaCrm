<?php

namespace App\Modules\Tenant\Reports\Application\Services;

use App\Modules\Core\VtigerModules\Contracts\ModuleRegistryInterface;
use App\Modules\Tenant\Reports\Domain\Models\Report;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\DB;

class ReportExecutionService
{
    private array $tableMetadata = [];

    public function __construct(
        private readonly ModuleRegistryInterface $moduleRegistry
    ) {
    }

    public function run(Report $report): \Illuminate\Support\Collection
    {
        // Increase memory limit for potentially large reports
        ini_set('memory_limit', '512M');

        $query = $this->buildQuery($report);

        return $query->get();
    }

    /**
     * Build the query for the report
     * 
     * @param Report $report
     * @return Builder
     */
    public function buildQuery(Report $report): Builder
    {
        $primaryModuleName = $report->modules->primarymodule;
        $primaryModule = $this->getModule($primaryModuleName);

        if (!$primaryModule) {
            throw new \InvalidArgumentException("Primary module '{$primaryModuleName}' not found or invalid");
        }

        $baseTable = $primaryModule->getBaseTable();
        $baseIndex = $primaryModule->getBaseIndex();

        // Track joined tables to avoid "Unknown column" errors from unjoined tables
        $joinedTables = [$baseTable];

        // 1. Base Query from Primary Module
        $query = DB::connection('tenant')->table($baseTable);

        // 2. Join Crmentity for Primary Module
        $crmentityAlias = "vtiger_crmentity" . $primaryModule->getName();
        if ($this->tableExists('vtiger_crmentity')) {
            $query->join("vtiger_crmentity as {$crmentityAlias}", "{$crmentityAlias}.crmid", '=', "{$baseTable}.{$baseIndex}");
            $query->where("{$crmentityAlias}.deleted", 0);
            $joinedTables[] = $crmentityAlias;
        }

        // 3. Join Custom Fields and other tables for Primary Module
        $primaryTables = $primaryModule->fields()->pluck('tableName')->unique();
        foreach ($primaryTables as $table) {
            if ($table === $baseTable || $table === 'vtiger_crmentity' || empty($table)) {
                continue;
            }

            // Only join if the table exists and has the base index column
            if ($this->columnExists($table, $baseIndex)) {
                $query->leftJoin($table, "{$table}.{$baseIndex}", '=', "{$baseTable}.{$baseIndex}");
                $joinedTables[] = $table;
            }
        }

        // 4. Handle Secondary Modules (Joins)
        $secondaryModules = $report->modules->secondarymodules;
        if ($secondaryModules) {
            $modules = explode(':', $secondaryModules);
            foreach ($modules as $modName) {
                if (empty($modName))
                    continue;
                $this->addSecondaryJoin($query, $primaryModule, $modName, $joinedTables);
            }
        }

        // 5. Select Columns
        $selectedColumns = $report->selectQuery->columns;
        foreach ($selectedColumns as $col) {
            $this->addColumnToSelect($query, $col->columnname, $joinedTables);
        }

        // 5.5 Standard Date Filter
        $this->applyStandardDateFilter($query, $report, $joinedTables);

        // 6. Apply Filters
        $filters = $report->selectQuery->criteria;

        // Group 1: All Conditions (AND)
        $allConditions = $filters->where('groupid', 1);
        if ($allConditions->isNotEmpty()) {
            $query->where(function ($q) use ($allConditions, $joinedTables) {
                foreach ($allConditions as $filter) {
                    $this->addFilterToQuery($q, 'where', $filter, $joinedTables);
                }
            });
        }

        // Group 2: Any Conditions (OR)
        $anyConditions = $filters->where('groupid', 2);
        if ($anyConditions->isNotEmpty()) {
            $query->where(function ($q) use ($anyConditions, $joinedTables) {
                foreach ($anyConditions as $filter) {
                    $this->addFilterToQuery($q, 'orWhere', $filter, $joinedTables);
                }
            });
        }

        return $query;
    }

    /**
     * Efficiently check if a table exists
     */
    private function tableExists(string $table): bool
    {
        $realTable = $this->getRealTableName($table);

        if (isset($this->tableMetadata[$realTable])) {
            return true;
        }

        $exists = DB::connection('tenant')->getSchemaBuilder()->hasTable($realTable);
        if ($exists) {
            $this->tableMetadata[$realTable] = null; // Cache existence
        }

        return $exists;
    }

    /**
     * Efficiently check if a column exists in a table
     */
    private function columnExists(string $table, string $column): bool
    {
        $realTable = $this->getRealTableName($table);

        if (!$this->tableExists($realTable)) {
            return false;
        }

        // Fetch all columns for the table once and cache them
        if ($this->tableMetadata[$realTable] === null) {
            $this->tableMetadata[$realTable] = DB::connection('tenant')->getSchemaBuilder()->getColumnListing($realTable);
        }

        return in_array($column, $this->tableMetadata[$realTable]);
    }

    /**
     * Resolve alias to real table name
     */
    private function getRealTableName(string $table): string
    {
        if (str_starts_with($table, 'vtiger_crmentity') && $table !== 'vtiger_crmentity') {
            return 'vtiger_crmentity';
        }
        return $table;
    }

    /**
     * Resolve module name, handling cases where it might be a table name
     */
    private function getModule(string $name): ?\App\Modules\Core\VtigerModules\Domain\ModuleDefinition
    {
        if ($this->moduleRegistry->has($name)) {
            return $this->moduleRegistry->get($name);
        }

        // Try to find module by base table name fallback (e.g. vtiger_contactdetails -> Contacts)
        return $this->moduleRegistry->all()->first(function ($module) use ($name) {
            return $module->getBaseTable() === $name;
        });
    }

    private function resolveColumn(string $vtigerColumn, array $joinedTables): ?array
    {
        $parts = explode(':', $vtigerColumn);
        if (count($parts) < 2)
            return null;

        $modName = $parts[0];
        $fieldName = $parts[1];

        $module = $this->getModule($modName);
        if (!$module)
            return null;

        $field = $module->getField($fieldName);
        if (!$field)
            return null;

        $table = $field->getTableName();
        $column = $field->getColumnName();

        // Handle aliased crmentity table
        if ($table === 'vtiger_crmentity') {
            $table = 'vtiger_crmentity' . $module->getName();
        }

        // Picklist logic: Check if the column exists in the base table first (Vtiger duplicates picklist values there)
        if (in_array($field->getUitype(), [15, 16, 33])) {
            $baseTable = $module->getBaseTable();
            if ($this->columnExists($baseTable, $fieldName)) {
                $table = $baseTable;
                $column = $fieldName;
            }
        }

        // Only add if table is joined
        if (!in_array($table, $joinedTables)) {
            // Try fallback to base table if not joined
            $baseTable = $module->getBaseTable();
            if (in_array($baseTable, $joinedTables) && $this->columnExists($baseTable, $column)) {
                $table = $baseTable;
            }
        }

        if (!in_array($table, $joinedTables)) {
            // Last resort: If it's a core crmentity field, use the primary crmentity alias
            if ($column === 'smownerid' || $column === 'createdtime' || $column === 'modifiedtime' || $column === 'crmid') {
                foreach ($joinedTables as $jt) {
                    if (str_starts_with($jt, 'vtiger_crmentity')) {
                        $table = $jt;
                        break;
                    }
                }
            }
        }

        if (!in_array($table, $joinedTables)) {
            return null;
        }

        return ['table' => $table, 'column' => $column, 'module' => $module, 'field' => $field];
    }

    private function addSecondaryJoin(Builder $query, $primaryModule, string $modName, array &$joinedTables): void
    {
        $targetModule = $this->getModule($modName);
        if (!$targetModule)
            return;

        $primaryTable = $primaryModule->getBaseTable();
        $primaryIndex = $primaryModule->getBaseIndex();
        $secondaryTable = $targetModule->getBaseTable();
        $secondaryIndex = $targetModule->getBaseIndex();

        if (in_array($secondaryTable, $joinedTables)) {
            return;
        }

        // 1. Direct link: Primary table points to secondary (e.g. Contact has accountid)
        if ($this->columnExists($primaryTable, $secondaryIndex)) {
            $query->leftJoin($secondaryTable, "{$secondaryTable}.{$secondaryIndex}", '=', "{$primaryTable}.{$secondaryIndex}");
            $joinedTables[] = $secondaryTable;
        }
        // 2. Inverse link: Secondary table points to primary (e.g. Ticket has contactid/contact_id)
        elseif ($this->columnExists($secondaryTable, $primaryIndex) || $this->columnExists($secondaryTable, str_replace('id', '_id', $primaryIndex))) {
            $linkCol = $this->columnExists($secondaryTable, $primaryIndex) ? $primaryIndex : str_replace('id', '_id', $primaryIndex);
            $query->leftJoin($secondaryTable, "{$secondaryTable}.{$linkCol}", '=', "{$primaryTable}.{$primaryIndex}");
            $joinedTables[] = $secondaryTable;
        }
        // 3. Fallback for CRM Entities: Linking through crmentity handles most Vtiger relations
        elseif ($primaryModule->isEntity() && $targetModule->isEntity()) {
            // We need to join the secondary table on its own index matching the CRM ID
            // But we need a way to link it to the primary table. 
            // Often this is via vtiger_crmentityrel or just direct parent/child.
            // For now, if no direct link found, try linking to the primary base index via secondaryIndex if it exists
            if ($this->columnExists($secondaryTable, 'parent_id')) {
                $query->leftJoin($secondaryTable, "{$secondaryTable}.parent_id", '=', "{$primaryTable}.{$primaryIndex}");
                $joinedTables[] = $secondaryTable;
            }
        }

        // Join secondary custom fields table
        if (in_array($secondaryTable, $joinedTables)) {
            $secondaryCfTable = "{$secondaryTable}cf";
            if ($this->tableExists($secondaryCfTable) && !in_array($secondaryCfTable, $joinedTables)) {
                $query->leftJoin($secondaryCfTable, "{$secondaryCfTable}.{$secondaryIndex}", '=', "{$secondaryTable}.{$secondaryIndex}");
                $joinedTables[] = $secondaryCfTable;
            }

            // Join crmentity for secondary module
            $crmentityAlias = "vtiger_crmentity" . $targetModule->getName();
            if ($this->tableExists('vtiger_crmentity') && !in_array($crmentityAlias, $joinedTables)) {
                $query->leftJoin("vtiger_crmentity as {$crmentityAlias}", "{$crmentityAlias}.crmid", '=', "{$secondaryTable}.{$secondaryIndex}");
                $joinedTables[] = $crmentityAlias;
            }
        }
    }

    private function addColumnToSelect(Builder $query, string $vtigerColumn, array &$joinedTables): void
    {
        $resolved = $this->resolveColumn($vtigerColumn, $joinedTables);
        if (!$resolved) {
            \Log::warning("Skipping column selection: {$vtigerColumn} - could not resolve to joined table");
            return;
        }

        $parts = explode(':', $vtigerColumn);
        $modPrefix = $parts[0] ?? $resolved['module']->getName();

        $table = $resolved['table'];
        $column = $resolved['column'];
        $field = $resolved['field'];
        $fieldName = $field->getFieldName();
        $alias = $modPrefix . '_' . $fieldName;
        $uitype = $field->getUitype();

        // Efficient column check
        if (!$this->columnExists($table, $column))
            return;

        // 1. Owner Fields (Users or Groups)
        if (in_array($uitype, [52, 53, 77])) {
            $userAlias = 'u_' . $alias;
            $groupAlias = 'g_' . $alias;

            $query->leftJoin("vtiger_users as {$userAlias}", "{$userAlias}.id", '=', "{$table}.{$column}");
            $query->leftJoin("vtiger_groups as {$groupAlias}", "{$groupAlias}.groupid", '=', "{$table}.{$column}");

            $query->addSelect(DB::raw("COALESCE(NULLIF(CONCAT_WS(' ', {$userAlias}.first_name, {$userAlias}.last_name), ' '), {$groupAlias}.groupname) as {$alias}"));

            // Add metadata for linking (Users don't have a generic detail view like modules, but we can pass anyway)
            $query->addSelect("{$table}.{$column} as {$alias}_id");
            $query->addSelect(DB::raw("CASE WHEN {$userAlias}.id IS NOT NULL THEN 'Users' ELSE 'Groups' END as {$alias}_module"));

            $joinedTables[] = $userAlias;
            $joinedTables[] = $groupAlias;
        }
        // 2. User-only references
        elseif (in_array($uitype, [51, 101])) {
            $userAlias = 'u_' . $alias;
            $query->leftJoin("vtiger_users as {$userAlias}", "{$userAlias}.id", '=', "{$table}.{$column}");
            $query->addSelect(DB::raw("NULLIF(CONCAT_WS(' ', {$userAlias}.first_name, {$userAlias}.last_name), ' ') as {$alias}"));

            $query->addSelect("{$table}.{$column} as {$alias}_id");
            $query->addSelect(DB::raw("'Users' as {$alias}_module"));

            $joinedTables[] = $userAlias;
        }
        // 3. General module references (UI 10, etc.)
        elseif (in_array($uitype, [10, 57, 58, 59, 73, 75, 76, 78, 80, 81])) {
            $entAlias = 'ent_' . $alias;
            $query->leftJoin("vtiger_crmentity as {$entAlias}", "{$entAlias}.crmid", '=', "{$table}.{$column}");
            $query->addSelect("{$entAlias}.label as {$alias}");

            $query->addSelect("{$table}.{$column} as {$alias}_id");
            $query->addSelect("{$entAlias}.setype as {$alias}_module");

            $joinedTables[] = $entAlias;
        }
        // 4. Standard Column
        else {
            $query->addSelect("{$table}.{$column} as {$alias}");
        }
    }

    private function applyStandardDateFilter(Builder $query, Report $report, array $joinedTables): void
    {
        $stdFilter = DB::connection('tenant')->table('vtiger_reportdatefilter')
            ->where('datefilterid', $report->reportid)->first();

        if (!$stdFilter || empty($stdFilter->datecolumnname) || $stdFilter->datefilter === 'custom') {
            // Handle custom dates if start/end are provided
            if ($stdFilter && $stdFilter->datefilter === 'custom' && $stdFilter->startdate && $stdFilter->enddate) {
                $resolved = $this->resolveColumn($stdFilter->datecolumnname, $joinedTables);
                if ($resolved) {
                    $query->whereBetween("{$resolved['table']}.{$resolved['column']}", [$stdFilter->startdate, $stdFilter->enddate]);
                }
            }
            return;
        }

        $resolved = $this->resolveColumn($stdFilter->datecolumnname, $joinedTables);
        if (!$resolved)
            return;

        $table = $resolved['table'];
        $column = $resolved['column'];
        $filter = $stdFilter->datefilter;

        $now = now();
        $startDate = null;
        $endDate = null;

        switch ($filter) {
            case 'today':
                $startDate = $now->copy()->startOfDay();
                $endDate = $now->copy()->endOfDay();
                break;
            case 'yesterday':
                $startDate = $now->copy()->subDay()->startOfDay();
                $endDate = $now->copy()->subDay()->endOfDay();
                break;
            case 'tomorrow':
                $startDate = $now->copy()->addDay()->startOfDay();
                $endDate = $now->copy()->addDay()->endOfDay();
                break;
            case 'lastweek':
                $startDate = $now->copy()->subWeek()->startOfWeek();
                $endDate = $now->copy()->subWeek()->endOfWeek();
                break;
            case 'thisweek':
                $startDate = $now->copy()->startOfWeek();
                $endDate = $now->copy()->endOfWeek();
                break;
            case 'nextweek':
                $startDate = $now->copy()->addWeek()->startOfWeek();
                $endDate = $now->copy()->addWeek()->endOfWeek();
                break;
            case 'lastmonth':
                $startDate = $now->copy()->subMonth()->startOfMonth();
                $endDate = $now->copy()->subMonth()->endOfMonth();
                break;
            case 'thismonth':
                $startDate = $now->copy()->startOfMonth();
                $endDate = $now->copy()->endOfMonth();
                break;
            case 'nextmonth':
                $startDate = $now->copy()->addMonth()->startOfMonth();
                $endDate = $now->copy()->addMonth()->endOfMonth();
                break;
            case 'last7days':
                $startDate = $now->copy()->subDays(7)->startOfDay();
                $endDate = $now->copy()->endOfDay();
                break;
            case 'last30days':
                $startDate = $now->copy()->subDays(30)->startOfDay();
                $endDate = $now->copy()->endOfDay();
                break;
            case 'last60days':
                $startDate = $now->copy()->subDays(60)->startOfDay();
                $endDate = $now->copy()->endOfDay();
                break;
            case 'last90days':
                $startDate = $now->copy()->subDays(90)->startOfDay();
                $endDate = $now->copy()->endOfDay();
                break;
            case 'last120days':
                $startDate = $now->copy()->subDays(120)->startOfDay();
                $endDate = $now->copy()->endOfDay();
                break;
            case 'next30days':
                $startDate = $now->copy()->startOfDay();
                $endDate = $now->copy()->addDays(30)->endOfDay();
                break;
            case 'next60days':
                $startDate = $now->copy()->startOfDay();
                $endDate = $now->copy()->addDays(60)->endOfDay();
                break;
            case 'next90days':
                $startDate = $now->copy()->startOfDay();
                $endDate = $now->copy()->addDays(90)->endOfDay();
                break;
            case 'next120days':
                $startDate = $now->copy()->startOfDay();
                $endDate = $now->copy()->addDays(120)->endOfDay();
                break;
            case 'thisfy':
                $startDate = $now->copy()->month >= 4 ? $now->copy()->month(4)->startOfMonth() : $now->copy()->subYear()->month(4)->startOfMonth();
                $endDate = $startDate->copy()->addYear()->subDay()->endOfDay();
                break;
            case 'prevfy':
                $currentFYStart = $now->copy()->month >= 4 ? $now->copy()->month(4)->startOfMonth() : $now->copy()->subYear()->month(4)->startOfMonth();
                $startDate = $currentFYStart->copy()->subYear();
                $endDate = $currentFYStart->copy()->subDay()->endOfDay();
                break;
            case 'nextfy':
                $currentFYStart = $now->copy()->month >= 4 ? $now->copy()->month(4)->startOfMonth() : $now->copy()->subYear()->month(4)->startOfMonth();
                $startDate = $currentFYStart->copy()->addYear();
                $endDate = $startDate->copy()->addYear()->subDay()->endOfDay();
                break;
        }

        if ($startDate && $endDate) {
            $query->whereBetween("{$table}.{$column}", [$startDate->toDateTimeString(), $endDate->toDateTimeString()]);
        }
    }

    private function addFilterToQuery(Builder $query, string $method, $filter, array $joinedTables): void
    {
        $resolved = $this->resolveColumn($filter->columnname, $joinedTables);
        if (!$resolved)
            return;

        $table = $resolved['table'];
        $column = $resolved['column'];
        $comparator = $filter->comparator;
        $value = $filter->value;

        switch ($comparator) {
            case 'e':
                $query->$method("{$table}.{$column}", '=', $value);
                break;
            case 'n':
                $query->$method("{$table}.{$column}", '<>', $value);
                break;
            case 's':
                $query->$method("{$table}.{$column}", 'LIKE', "{$value}%");
                break;
            case 'ew':
                $query->$method("{$table}.{$column}", 'LIKE', "%{$value}");
                break;
            case 'c':
                $query->$method("{$table}.{$column}", 'LIKE', "%{$value}%");
                break;
            case 'k':
                $query->$method("{$table}.{$column}", 'NOT LIKE', "%{$value}%");
                break;
            case 'l':
            case 'b': // before
                $query->$method("{$table}.{$column}", '<', $value);
                break;
            case 'g':
            case 'a': // after
                $query->$method("{$table}.{$column}", '>', $value);
                break;
            case 'm':
                $query->$method("{$table}.{$column}", '<=', $value);
                break;
            case 'h':
                $query->$method("{$table}.{$column}", '>=', $value);
                break;
            case 'bw':
                $parts = explode(',', $value);
                if (count($parts) == 2) {
                    $query->{$method . 'Between'}("{$table}.{$column}", [trim($parts[0]), trim($parts[1])]);
                }
                break;
            case 'y': // is empty
                $query->$method(function ($q) use ($table, $column) {
                    $q->whereNull("{$table}.{$column}")->orWhere("{$table}.{$column}", '=', '');
                });
                break;
            case 'ny': // is not empty
                $query->$method(function ($q) use ($table, $column) {
                    $q->whereNotNull("{$table}.{$column}")->where("{$table}.{$column}", '<>', '');
                });
                break;
            default:
                $query->$method("{$table}.{$column}", '=', $value);
        }
    }
}
