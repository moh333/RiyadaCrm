<?php

namespace App\Modules\Tenant\Reports\Presentation\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Tenant\Reports\Domain\Models\Report;
use App\Modules\Tenant\Reports\Domain\Models\ReportFolder;
use App\Modules\Tenant\Reports\Application\Services\ReportService;
use App\Modules\Core\VtigerModules\Contracts\ModuleRegistryInterface;
use App\Models\Tenant\VtigerUser;
use App\Models\Tenant\VtigerGroup;
use App\Models\Tenant\VtigerRole;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ReportsController extends Controller
{
    public function __construct(
        private readonly ReportService $reportService,
        private readonly ModuleRegistryInterface $moduleRegistry
    ) {
    }

    public function index()
    {
        $folders = ReportFolder::all();

        return view('reports::index', compact('folders'));
    }

    /**
     * Get reports data for server-side DataTables
     */
    public function datatable(Request $request)
    {
        $query = Report::with(['folder', 'modules']);

        // Filter by folder
        if ($request->filled('folder')) {
            $query->where('folderid', $request->folder);
        }

        // Search
        if ($request->filled('search.value')) {
            $search = $request->input('search.value');
            $query->where(function ($q) use ($search) {
                $q->where('reportname', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%");
            });
        }

        // Get total count before filtering
        $totalCount = Report::count();

        // Get filtered count
        $filteredCount = $query->count();

        // Sorting
        $orderColumn = $request->input('order.0.column', 0);
        $orderDir = $request->input('order.0.dir', 'asc');
        $columns = ['reportname', 'primarymodule', 'foldername', 'actions'];

        if ($orderColumn == 0) {
            $query->orderBy('reportname', $orderDir);
        }

        // Pagination
        $start = $request->input('start', 0);
        $length = $request->input('length', 10);

        $reports = $query->skip($start)->take($length)->get();

        // Format data for DataTables
        $data = $reports->map(function ($report) {
            $primaryModule = $report->modules->primarymodule ?? '-';

            // Safely translate module name
            try {
                $translatedModule = vtranslate($primaryModule);
            } catch (\Exception $e) {
                // If translation fails, just use the raw module name
                $translatedModule = $primaryModule;
            }

            return [
                'reportid' => $report->reportid,
                'reportname' => $report->reportname,
                'description' => $report->description,
                'primarymodule' => $translatedModule,
                'foldername' => $report->folder->foldername ?? null,
                'folderid' => $report->folderid,
                'actions' => '' // Actions are rendered client-side
            ];
        });

        // Get folder counts
        $folderCounts = [
            'total' => $totalCount,
            'folders' => Report::selectRaw('folderid, count(*) as count')
                ->groupBy('folderid')
                ->pluck('count', 'folderid')
                ->toArray()
        ];

        return response()->json([
            'draw' => intval($request->input('draw')),
            'recordsTotal' => $totalCount,
            'recordsFiltered' => $filteredCount,
            'data' => $data,
            'folderCounts' => $folderCounts
        ]);
    }

    public function create()
    {
        $folders = ReportFolder::all();
        $activeModules = $this->moduleRegistry->getActive();
        $users = VtigerUser::all();
        $groups = VtigerGroup::all();
        $roles = VtigerRole::all();

        return view('reports::create', compact('folders', 'activeModules', 'users', 'groups', 'roles'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'reportname' => 'required|string|max:255',
            'folderid' => 'required|integer',
            'primarymodule' => 'required|string',
        ]);

        $report = $this->reportService->store($request->all());

        return redirect()->route('tenant.reports.index')->with('success', __('reports::reports.report_created_success'));
    }

    public function show($id)
    {
        $report = Report::with([
            'modules',
            'selectQuery.columns',
            'selectQuery.criteria',
            'folder',
            'shareUsers.user',
            'shareGroups',
            'shareRoles',
            'scheduledReport'
        ])->findOrFail($id);

        // Get report columns for display
        $columns = [];
        if ($report->selectQuery && $report->selectQuery->columns) {
            foreach ($report->selectQuery->columns as $column) {
                $parts = explode(':', $column->columnname);
                $module = $parts[0] ?? '';
                $label = isset($parts[2]) ? base64_decode($parts[2]) : ($parts[1] ?? '');

                $columns[] = [
                    'module' => vtranslate($module, 'Vtiger'),
                    'field' => $parts[1] ?? '',
                    'label' => vtranslate($label, $module),
                    'raw' => $column->columnname
                ];
            }
        }

        // Execute report
        $executionService = app(\App\Modules\Tenant\Reports\Application\Services\ReportExecutionService::class);
        $rawData = $executionService->run($report);

        // Translate table data headers
        $data = $this->translateData($rawData, $report);

        return view('reports::show', compact('report', 'data', 'columns'));
    }

    /**
     * Map raw data keys (Module_Field) to translated labels
     */
    private function translateData($rawData, Report $report): array
    {
        if ($rawData->isEmpty())
            return [];

        // Increase memory limit for processing
        ini_set('memory_limit', '512M');

        $headerMap = [];
        if ($report->selectQuery && $report->selectQuery->columns) {
            foreach ($report->selectQuery->columns as $column) {
                $parts = explode(':', $column->columnname);
                $module = $parts[0] ?? '';
                $field = $parts[1] ?? '';
                $label = isset($parts[2]) ? base64_decode($parts[2]) : ($parts[1] ?? '');

                $alias = $module . '_' . $field;
                $headerMap[$alias] = vtranslate($label, $module);
            }
        }

        return $rawData->map(function ($row) use ($headerMap) {
            $newRow = [];
            foreach ((array) $row as $key => $value) {
                $translatedKey = $headerMap[$key] ?? $key;
                $newRow[$translatedKey] = $value ?? '';
            }
            return $newRow;
        })->all();
    }

    public function edit($id)
    {
        $report = Report::with(['modules', 'selectQuery.columns', 'selectQuery.criteria', 'shareUsers', 'shareGroups', 'shareRoles', 'scheduledReport'])->findOrFail($id);
        $folders = ReportFolder::all();
        $activeModules = $this->moduleRegistry->getActive();
        $users = VtigerUser::all();
        $groups = VtigerGroup::all();
        $roles = VtigerRole::all();

        $secondaryModules = [];
        if (!empty($report->modules->secondarymodules)) {
            $secondaryNames = explode(':', $report->modules->secondarymodules);
            foreach ($secondaryNames as $name) {
                if ($name) {
                    try {
                        $mod = $this->moduleRegistry->get($name);
                        $secondaryModules[] = [
                            'name' => $mod->getName(),
                            'label' => $mod->getLabel()
                        ];
                    } catch (\Exception $e) {
                        $secondaryModules[] = ['name' => $name, 'label' => vtranslate($name)];
                    }
                }
            }
        }

        return view('reports::edit', compact('report', 'folders', 'activeModules', 'users', 'groups', 'roles', 'secondaryModules'));
    }

    public function update(Request $request, $id)
    {
        $validated = $request->validate([
            'reportname' => 'required|string|max:255',
            'folderid' => 'required|integer',
        ]);

        $report = Report::findOrFail($id);
        $this->reportService->update($report, $request->all());

        return redirect()->route('tenant.reports.index')->with('success', __('reports::reports.report_updated_success'));
    }

    public function destroy($id)
    {
        $report = Report::findOrFail($id);
        $this->reportService->delete($report);

        return response()->json(['success' => true]);
    }

    public function run($id)
    {
        return $this->show($id);
    }

    /**
     * Export report to CSV or Excel format
     */
    public function export(Request $request, $id): StreamedResponse
    {
        $report = Report::with(['modules', 'selectQuery.columns', 'selectQuery.criteria'])->findOrFail($id);
        $format = $request->query('format', 'csv');

        // Execute the report to get data
        $executionService = app(\App\Modules\Tenant\Reports\Application\Services\ReportExecutionService::class);
        $rawData = $executionService->run($report);
        $data = $this->translateData($rawData, $report);

        $filename = str_replace([' ', '/'], '_', $report->reportname) . '_' . date('Y-m-d_H-i-s');

        if ($format === 'xls' || $format === 'xlsx') {
            return $this->exportToExcel($report, $data, $filename);
        }

        return $this->exportToCsv($report, $data, $filename);
    }

    /**
     * Export to CSV format with UTF-8 BOM for Excel compatibility
     */
    protected function exportToCsv($report, array $data, string $filename): StreamedResponse
    {
        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"{$filename}.csv\"",
            'Pragma' => 'no-cache',
            'Cache-Control' => 'must-revalidate, post-check=0, pre-check=0',
            'Expires' => '0'
        ];

        return response()->stream(function () use ($data) {
            // UTF-8 BOM - critical for Excel to recognize UTF-8
            echo chr(0xEF) . chr(0xBB) . chr(0xBF);

            if (count($data) > 0) {
                // Write headers
                $headerRow = array_keys($data[0]);
                echo '"' . implode('","', array_map(fn($h) => str_replace('"', '""', $h), $headerRow)) . '"' . "\n";

                // Write data rows
                foreach ($data as $row) {
                    echo '"' . implode('","', array_map(fn($v) => str_replace('"', '""', $v ?? ''), array_values($row))) . '"' . "\n";
                }
            }
        }, 200, $headers);
    }

    /**
     * Export to Excel format using UTF-16LE encoding for proper Arabic support
     */
    protected function exportToExcel($report, array $data, string $filename): StreamedResponse
    {
        $headers = [
            'Content-Type' => 'text/plain; charset=UTF-16LE',
            'Content-Disposition' => "attachment; filename=\"{$filename}.txt\"",
            'Pragma' => 'no-cache',
            'Cache-Control' => 'must-revalidate, post-check=0, pre-check=0',
            'Expires' => '0'
        ];

        return response()->stream(function () use ($data) {
            // UTF-16LE BOM - MUST be first
            echo chr(0xFF) . chr(0xFE);

            if (count($data) > 0) {
                // Convert headers to UTF-16LE
                $headerRow = array_keys($data[0]);
                $headerLine = implode("\t", $headerRow);
                echo mb_convert_encoding($headerLine . "\n", 'UTF-16LE', 'UTF-8');

                // Convert data rows to UTF-16LE
                foreach ($data as $row) {
                    $line = implode("\t", array_values($row));
                    echo mb_convert_encoding($line . "\n", 'UTF-16LE', 'UTF-8');
                }
            }
        }, 200, $headers);
    }

    public function getConditionOperators()
    {
        return response()->json([
            'operators' => [
                'e' => __('tenant::settings.operator_is'),
                'n' => __('tenant::settings.operator_is_not'),
                's' => __('tenant::settings.operator_contains'),
                'ew' => __('tenant::settings.operator_ends_with'),
                'c' => __('tenant::settings.operator_contains'),
                'k' => __('tenant::settings.operator_does_not_contain'),
                'l' => __('tenant::settings.operator_less_than'),
                'g' => __('tenant::settings.operator_greater_than'),
                'm' => __('tenant::settings.operator_less_than_or_equal'),
                'h' => __('tenant::settings.operator_greater_than_or_equal'),
                'b' => __('tenant::settings.operator_before'),
                'a' => __('tenant::settings.operator_after'),
                'bw' => __('tenant::settings.operator_between'),
            ]
        ]);
    }
}
