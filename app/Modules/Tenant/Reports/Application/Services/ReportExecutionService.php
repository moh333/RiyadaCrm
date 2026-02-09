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
        }
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
            if ($column === 'smownerid' || $column === 'createdtime' || $column === 'modifiedtime') {
                foreach ($joinedTables as $jt) {
                    if (str_starts_with($jt, 'vtiger_crmentity')) {
                        $table = $jt;
                        break;
                    }
                }
            }
        }

        if (!in_array($table, $joinedTables)) {
            \Log::warning("Skipping column selection from unjoined table: {$table}.{$column} for field {$fieldName}");
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
