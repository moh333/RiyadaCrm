<?php

namespace App\Modules\Tenant\Reports\Application\Services;

use App\Modules\Tenant\Reports\Domain\Models\Report;
use App\Modules\Tenant\Reports\Domain\Models\ReportModule;
use App\Modules\Tenant\Reports\Domain\Models\SelectColumn;
use App\Modules\Tenant\Reports\Domain\Models\SelectQuery;
use Illuminate\Support\Facades\DB;

class ReportService
{
    /**
     * Store a new report and its configuration
     * 
     * @param array $data
     * @return Report
     */
    public function store(array $data): Report
    {
        return DB::connection('tenant')->transaction(function () use ($data) {
            // 1. Create Select Query
            $query = SelectQuery::create([]);
            $queryId = $query->queryid;

            // 2. Add Selected Columns
            if (isset($data['columns'])) {
                foreach ($data['columns'] as $index => $colString) {
                    SelectColumn::create([
                        'queryid' => $queryId,
                        'columnindex' => $index,
                        'columnname' => $colString // Format: Module:Field:Label:Type
                    ]);
                }
            }

            // 3. Create Main Report Record
            $report = Report::create([
                'reportname' => $data['reportname'],
                'folderid' => $data['folderid'],
                'description' => $data['description'] ?? '',
                'reporttype' => $data['report_type'] ?? 'tabular',
                'queryid' => $queryId,
                'owner' => auth('tenant')->id(),
                'state' => 'SAVED',
                'customizable' => 1
            ]);

            // 4. Create Report Modules Linkage
            ReportModule::create([
                'reportmodulesid' => $report->reportid,
                'primarymodule' => $data['primarymodule'],
                'secondarymodules' => isset($data['secondarymodules']) ? implode(':', $data['secondarymodules']) : ''
            ]);

            // 5. TODO: Add Filters (Standard and Advanced)

            return $report;
        });
    }

    /**
     * Update an existing report
     */
    public function update(Report $report, array $data): Report
    {
        return DB::connection('tenant')->transaction(function () use ($report, $data) {
            $report->update([
                'reportname' => $data['reportname'],
                'folderid' => $data['folderid'],
                'description' => $data['description'] ?? '',
            ]);

            $report->modules->update([
                'primarymodule' => $data['primarymodule'],
                'secondarymodules' => isset($data['secondarymodules']) ? implode(':', $data['secondarymodules']) : ''
            ]);

            // Update columns
            $report->selectQuery->columns()->delete();
            if (isset($data['columns'])) {
                foreach ($data['columns'] as $index => $colString) {
                    SelectColumn::create([
                        'queryid' => $report->queryid,
                        'columnindex' => $index,
                        'columnname' => $colString
                    ]);
                }
            }

            return $report->fresh();
        });
    }

    /**
     * Delete a report and all its configuration
     */
    public function delete(Report $report): void
    {
        DB::connection('tenant')->transaction(function () use ($report) {
            $report->selectQuery->columns()->delete();
            $report->selectQuery->criteria()->delete();
            $report->selectQuery->criteriaGroupings()->delete();
            $report->selectQuery->delete();
            $report->modules()->delete();
            $report->sortColumns()->delete();
            $report->groupByColumns()->delete();
            $report->summaries()->delete();
            $report->delete();
        });
    }
}
