<?php

namespace App\Modules\Tenant\Reports\Application\Services;

use App\Modules\Tenant\Reports\Domain\Models\Report;
use App\Modules\Tenant\Reports\Domain\Models\ReportModule;
use App\Modules\Tenant\Reports\Domain\Models\SelectColumn;
use App\Modules\Tenant\Reports\Domain\Models\SelectQuery;
use App\Models\Tenant\VtigerReportShareuser;
use App\Models\Tenant\VtigerReportSharegroup;
use App\Models\Tenant\VtigerReportSharerole;
use App\Models\Tenant\VtigerScheduledReport;
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

            // 5. Handle Filters (Standard and Advanced)
            if (isset($data['std_field']) && !empty($data['std_field'])) {
                DB::connection('tenant')->table('vtiger_reportdatefilter')->insert([
                    'datefilterid' => $report->reportid,
                    'datecolumnname' => $data['std_field'],
                    'datefilter' => $data['std_duration'] ?? 'custom',
                    'startdate' => $data['startdate'] ?? null,
                    'enddate' => $data['enddate'] ?? null,
                ]);
            }

            if (isset($data['conditions'])) {
                foreach ($data['conditions'] as $index => $condition) {
                    \App\Modules\Tenant\Reports\Domain\Models\RelCriteria::create([
                        'queryid' => $queryId,
                        'columnindex' => $index,
                        'columnname' => $condition['columnname'],
                        'comparator' => $condition['comparator'],
                        'value' => $condition['value'] ?? '',
                        'groupid' => $condition['groupid'] ?? 1,
                        'column_condition' => 'AND' // Default to AND within group
                    ]);
                }

                // Create default groupings if they don't exist
                DB::connection('tenant')->table('vtiger_relcriteria_grouping')->insertOrIgnore([
                    ['groupid' => 1, 'queryid' => $queryId, 'group_condition' => 'AND', 'condition_expression' => ''],
                    ['groupid' => 2, 'queryid' => $queryId, 'group_condition' => 'OR', 'condition_expression' => '']
                ]);
            }

            // 6. Handle Sharing
            if (isset($data['sharing'])) {
                foreach ($data['sharing'] as $shareString) {
                    [$type, $id] = explode(':', $shareString);
                    if ($type === 'users') {
                        VtigerReportShareuser::create(['reportid' => $report->reportid, 'userid' => $id]);
                    } elseif ($type === 'groups') {
                        VtigerReportSharegroup::create(['reportid' => $report->reportid, 'groupid' => $id]);
                    } elseif ($type === 'roles' || $type === 'rolesandsubordinates') {
                        VtigerReportSharerole::create(['reportid' => $report->reportid, 'roleid' => $id]);
                    }
                }
            }

            // 7. Handle Scheduling
            if (isset($data['is_scheduled']) && $data['is_scheduled']) {
                $scheduleData = [
                    'reportid' => $report->reportid,
                    'scheduleid' => $data['scheduleid'] ?? 1,
                    'schtime' => $data['schtime'] ?? '09:00:00',
                    'recipients' => isset($data['recipients']) ? json_encode($data['recipients']) : '[]',
                    'specificemails' => !empty($data['specificemails'])
                        ? json_encode(array_map('trim', explode(',', $data['specificemails'])))
                        : '[]',
                    'fileformat' => $data['fileformat'] ?? 'CSV',
                ];

                // Handle schedule-type specific fields
                $scheduleId = (int) ($data['scheduleid'] ?? 1);

                if ($scheduleId === 2) { // Weekly
                    $scheduleData['schdayoftheweek'] = isset($data['schdayoftheweek'])
                        ? json_encode($data['schdayoftheweek'])
                        : '[]';
                }

                if ($scheduleId === 3) { // Monthly by date
                    $scheduleData['schdayofthemonth'] = isset($data['schdayofthemonth'])
                        ? json_encode($data['schdayofthemonth'])
                        : '[]';
                }

                if ($scheduleId === 4) { // Annually
                    $scheduleData['schannualdates'] = $data['schannualdates'] ?? '[]';
                }

                if ($scheduleId === 5) { // Specific date
                    $scheduleData['schdate'] = isset($data['schdate'])
                        ? json_encode([$data['schdate']])
                        : null;
                }

                $schedule = VtigerScheduledReport::create($scheduleData);

                // Calculate and set next trigger time
                $schedule->next_trigger_time = $schedule->calculateNextTriggerTime();
                $schedule->save();
            }

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

            // Update Filters
            DB::connection('tenant')->table('vtiger_reportdatefilter')->where('datefilterid', $report->reportid)->delete();
            if (isset($data['std_field']) && !empty($data['std_field'])) {
                DB::connection('tenant')->table('vtiger_reportdatefilter')->insert([
                    'datefilterid' => $report->reportid,
                    'datecolumnname' => $data['std_field'],
                    'datefilter' => $data['std_duration'] ?? 'custom',
                    'startdate' => $data['startdate'] ?? null,
                    'enddate' => $data['enddate'] ?? null,
                ]);
            }

            $report->selectQuery->criteria()->delete();
            if (isset($data['conditions'])) {
                foreach ($data['conditions'] as $index => $condition) {
                    \App\Modules\Tenant\Reports\Domain\Models\RelCriteria::create([
                        'queryid' => $report->queryid,
                        'columnindex' => $index,
                        'columnname' => $condition['columnname'],
                        'comparator' => $condition['comparator'],
                        'value' => $condition['value'] ?? '',
                        'groupid' => $condition['groupid'] ?? 1,
                        'column_condition' => 'AND'
                    ]);
                }
            }

            // Update columns
            $report->selectQuery->columns()->delete();
            if (isset($data['columns'])) {
                foreach ($data['columns'] as $index => $colString) {
                    \App\Modules\Tenant\Reports\Domain\Models\SelectColumn::create([
                        'queryid' => $report->queryid,
                        'columnindex' => $index,
                        'columnname' => $colString
                    ]);
                }
            }

            // Update Sharing
            $report->shareUsers()->delete();
            $report->shareGroups()->delete();
            $report->shareRoles()->delete();
            if (isset($data['sharing'])) {
                foreach ($data['sharing'] as $shareString) {
                    [$type, $id] = explode(':', $shareString);
                    if ($type === 'users') {
                        VtigerReportShareuser::create(['reportid' => $report->reportid, 'userid' => $id]);
                    } elseif ($type === 'groups') {
                        VtigerReportSharegroup::create(['reportid' => $report->reportid, 'groupid' => $id]);
                    } elseif ($type === 'roles' || $type === 'rolesandsubordinates') {
                        VtigerReportSharerole::create(['reportid' => $report->reportid, 'roleid' => $id]);
                    }
                }
            }

            // Update Scheduling
            $report->scheduledReport()->delete();
            if (isset($data['is_scheduled']) && $data['is_scheduled']) {
                $scheduleData = [
                    'reportid' => $report->reportid,
                    'scheduleid' => $data['scheduleid'] ?? 1,
                    'schtime' => $data['schtime'] ?? '09:00:00',
                    'recipients' => isset($data['recipients']) ? json_encode($data['recipients']) : '[]',
                    'specificemails' => !empty($data['specificemails'])
                        ? json_encode(array_map('trim', explode(',', $data['specificemails'])))
                        : '[]',
                    'fileformat' => $data['fileformat'] ?? 'CSV',
                ];

                // Handle schedule-type specific fields
                $scheduleId = (int) ($data['scheduleid'] ?? 1);

                if ($scheduleId === 2) { // Weekly
                    $scheduleData['schdayoftheweek'] = isset($data['schdayoftheweek'])
                        ? json_encode($data['schdayoftheweek'])
                        : '[]';
                }

                if ($scheduleId === 3) { // Monthly by date
                    $scheduleData['schdayofthemonth'] = isset($data['schdayofthemonth'])
                        ? json_encode($data['schdayofthemonth'])
                        : '[]';
                }

                if ($scheduleId === 4) { // Annually
                    $scheduleData['schannualdates'] = $data['schannualdates'] ?? '[]';
                }

                if ($scheduleId === 5) { // Specific date
                    $scheduleData['schdate'] = isset($data['schdate'])
                        ? json_encode([$data['schdate']])
                        : null;
                }

                $schedule = VtigerScheduledReport::create($scheduleData);

                // Calculate and set next trigger time
                $schedule->next_trigger_time = $schedule->calculateNextTriggerTime();
                $schedule->save();
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
            // Delete select query related data
            $report->selectQuery->columns()->delete();
            $report->selectQuery->criteria()->delete();
            $report->selectQuery->criteriaGroupings()->delete();
            $report->selectQuery->delete();

            // Delete report modules
            $report->modules()->delete();

            // Delete sorting and grouping
            $report->sortColumns()->delete();
            $report->groupByColumns()->delete();
            $report->summaries()->delete();

            // Delete sharing
            $report->shareUsers()->delete();
            $report->shareGroups()->delete();
            $report->shareRoles()->delete();

            // Delete scheduling
            $report->scheduledReport()->delete();

            // Delete date filter
            DB::connection('tenant')->table('vtiger_reportdatefilter')
                ->where('datefilterid', $report->reportid)->delete();

            // Delete the report itself
            $report->delete();
        });
    }
}
