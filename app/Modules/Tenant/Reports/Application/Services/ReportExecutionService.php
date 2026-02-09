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

        // 6. Apply Filters
        $filters = $report->selectQuery->criteria;
        foreach ($filters as $filter) {
            $this->addFilterToQuery($query, $filter, $joinedTables);
        }

        return $query;
    }

    /**
     * Efficiently check if a table exists
     */
    private function tableExists(string $table): bool
    {
        if (isset($this->tableMetadata[$table])) {
            return true;
        }

        $exists = DB::connection('tenant')->getSchemaBuilder()->hasTable($table);
        if ($exists) {
            $this->tableMetadata[$table] = null; // Cache existence
        }

        return $exists;
    }

    /**
     * Efficiently check if a column exists in a table
     */
    private function columnExists(string $table, string $column): bool
    {
        if (!$this->tableExists($table)) {
            return false;
        }

        // Fetch all columns for the table once and cache them
        if ($this->tableMetadata[$table] === null) {
            $this->tableMetadata[$table] = DB::connection('tenant')->getSchemaBuilder()->getColumnListing($table);
        }

        return in_array($column, $this->tableMetadata[$table]);
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

    private function addSecondaryJoin(Builder $query, $primaryModule, string $modName, array &$joinedTables): void
    {
        $targetModule = $this->getModule($modName);
        if (!$targetModule)
            return;

        // Simplistic join logic for now
    }

    private function addColumnToSelect(Builder $query, string $vtigerColumn, array &$joinedTables): void
    {
        $parts = explode(':', $vtigerColumn);
        if (count($parts) < 2)
            return;

        $modName = $parts[0];
        $fieldName = $parts[1];

        $module = $this->getModule($modName);
        if (!$module)
            return;

        $field = $module->getField($fieldName);
        if (!$field)
            return;

        $table = $field->getTableName();
        $column = $field->getColumnName();
        $alias = $modName . '_' . $fieldName;

        // Handle aliased crmentity table
        if ($table === 'vtiger_crmentity') {
            $table = 'vtiger_crmentity' . $module->getName();
        }

        // Picklist logic
        if (in_array($field->getUitype(), [15, 16, 33]) && str_starts_with($table, 'vtiger_')) {
            $column = $fieldName;
        }

        // Only add if table is joined
        if (!in_array($table, $joinedTables)) {
            $primaryModule = $this->getModule($field->getModule());
            if ($primaryModule && $table !== $primaryModule->getBaseTable()) {
                $table = $primaryModule->getBaseTable();
            }
        }

        if (!in_array($table, $joinedTables)) {
            return;
        }

        // Efficient column check
        if ($this->columnExists($table, $column)) {
            $query->addSelect("{$table}.{$column} as {$alias}");
        }
    }

    private function addFilterToQuery(Builder $query, $filter, array $joinedTables): void
    {
        // TODO: Map VTiger comparators (e, c, bw, etc.) to SQL
    }
}
