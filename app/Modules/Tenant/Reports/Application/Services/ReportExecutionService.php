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

        return $query->get()->toArray();
    }

    /**
     * Build the query for the report
     * 
     * @param Report $report
     * @return Builder
     */
    public function buildQuery(Report $report): Builder
    {
        $primaryModule = $this->moduleRegistry->get($report->modules->primarymodule);

        // 1. Base Query from Primary Module
        $query = DB::connection('tenant')->table($primaryModule->getBaseTable() . ' as primary_table');

        // 2. Join Crmentity for Primary Module
        $query->join('vtiger_crmentity as primary_crmentity', 'primary_crmentity.crmid', '=', 'primary_table.' . $primaryModule->getBaseIndex());
        $query->where('primary_crmentity.deleted', 0);

        // 3. Join Custom Fields table if exists
        $cfTable = $primaryModule->getBaseTable() . 'cf';
        if (DB::connection('tenant')->getSchemaBuilder()->hasTable($cfTable)) {
            $query->join($cfTable . ' as primary_cf', 'primary_cf.' . $primaryModule->getBaseIndex(), '=', 'primary_table.' . $primaryModule->getBaseIndex());
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

    private function addSecondaryJoin(Builder $query, $primaryModule, string $modName): void
    {
        // Simplistic join logic - usually VTiger uses fieldmodulerel or simple related field
        // This needs to be much more robust for a full implementation
        $targetModule = $this->moduleRegistry->get($modName);

        // Logic to find join field in primary module to target module
        // For now, let's assume a standard lookup field or use metadata
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

        $module = $this->moduleRegistry->get($modName);
        $field = $module->getField($fieldName);

        if (!$field)
            return;

        $table = $field->getTableName();
        $column = $field->getColumnName();
        $alias = $modName . '_' . $fieldName;

        $query->addSelect("$table.$column as $alias");
    }

    private function addFilterToQuery(Builder $query, $filter): void
    {
        // TODO: Map VTiger comparators (e, c, bw, etc.) to SQL
    }
}
