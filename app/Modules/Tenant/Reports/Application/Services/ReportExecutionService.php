<?php

namespace App\Modules\Tenant\Reports\Application\Services;

use App\Modules\Core\VtigerModules\Contracts\ModuleRegistryInterface;
use App\Modules\Tenant\Reports\Domain\Models\Report;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\DB;

class ReportExecutionService
{
    public function __construct(
        private readonly ModuleRegistryInterface $moduleRegistry
    ) {
    }

    /**
     * Execute a report and return results
     * 
     * @param Report $report
     * @return array
     */
    public function run(Report $report): array
    {
        $query = $this->buildQuery($report);

        return $query->get()->map(fn($item) => (array) $item)->toArray();
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

        // 1. Base Query from Primary Module (No alias needed for base table to allow direct access)
        $query = DB::connection('tenant')->table($baseTable);

        // 2. Join Crmentity for Primary Module (Alias with module name to avoid collisions with secondary modules)
        $crmentityAlias = "vtiger_crmentity" . $primaryModule->getName();
        $query->join("vtiger_crmentity as {$crmentityAlias}", "{$crmentityAlias}.crmid", '=', "{$baseTable}.{$baseIndex}");
        $query->where("{$crmentityAlias}.deleted", 0);

        // 3. Join Custom Fields and other tables for Primary Module
        // We look at all fields to find which tables they belong to
        $primaryTables = $primaryModule->fields()->pluck('tableName')->unique();
        foreach ($primaryTables as $table) {
            if ($table === $baseTable || $table === 'vtiger_crmentity' || empty($table)) {
                continue;
            }
            // Join on the base index
            $query->leftJoin($table, "{$table}.{$baseIndex}", '=', "{$baseTable}.{$baseIndex}");
        }

        // 4. Handle Secondary Modules (Joins)
        $secondaryModules = $report->modules->secondarymodules;
        if ($secondaryModules) {
            $modules = explode(':', $secondaryModules);
            foreach ($modules as $modName) {
                if (empty($modName))
                    continue;
                $this->addSecondaryJoin($query, $primaryModule, $modName);
            }
        }

        // 5. Select Columns
        $selectedColumns = $report->selectQuery->columns;
        foreach ($selectedColumns as $col) {
            $this->addColumnToSelect($query, $col->columnname);
        }

        // 6. Apply Filters
        $filters = $report->selectQuery->criteria;
        foreach ($filters as $filter) {
            $this->addFilterToQuery($query, $filter);
        }

        return $query;
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

    private function addSecondaryJoin(Builder $query, $primaryModule, string $modName): void
    {
        $targetModule = $this->getModule($modName);
        if (!$targetModule)
            return;

        // Simplistic join logic for now
        // A full implementation would use vtiger_relatedlists or fieldmodulerel
    }

    private function addColumnToSelect(Builder $query, string $vtigerColumn): void
    {
        // VTiger column format: "Module:FieldName:Label:FieldType"
        // e.g. "Accounts:accountname:Account_Name:V"
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

        $query->addSelect("{$table}.{$column} as {$alias}");
    }

    private function addFilterToQuery(Builder $query, $filter): void
    {
        // TODO: Map VTiger comparators (e, c, bw, etc.) to SQL
    }
}
