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
        $reports = Report::with(['folder', 'modules'])->get();

        return view('reports::index', compact('folders', 'reports'));
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
        $report = $this->reportService->store($request->all());

        return redirect()->route('tenant.reports.index')->with('success', 'Report created successfully');
    }

    public function show($id)
    {
        $report = Report::findOrFail($id);

        $executionService = app(\App\Modules\Tenant\Reports\Application\Services\ReportExecutionService::class);
        $data = $executionService->run($report);

        return view('reports::show', compact('report', 'data'));
    }

    public function edit($id)
    {
        $report = Report::with(['modules', 'selectQuery.columns', 'shareUsers', 'shareGroups', 'shareRoles', 'scheduledReport'])->findOrFail($id);
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
        $report = Report::findOrFail($id);
        $this->reportService->update($report, $request->all());

        return redirect()->route('tenant.reports.index')->with('success', 'Report updated successfully');
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
}
