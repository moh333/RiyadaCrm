<?php

declare(strict_types=1);

namespace App\Modules\Tenant\Settings\Presentation\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Tenant\ComVtigerWorkflow;
use App\Models\Tenant\ComVtigerWorkflowtask;
use Illuminate\Http\Request;
use Illuminate\View\View;

class WorkflowController extends Controller
{
    /**
     * Display a listing of workflows
     */
    public function index(Request $request): View
    {
        $moduleFilter = $request->get('module', 'all');

        $query = ComVtigerWorkflow::query();

        if ($moduleFilter !== 'all') {
            $query->where('module_name', $moduleFilter);
        }

        $workflows = $query->orderBy('module_name')
            ->orderBy('workflowname')
            ->get();

        // Get unique modules for filter
        $modules = ComVtigerWorkflow::select('module_name')
            ->distinct()
            ->orderBy('module_name')
            ->pluck('module_name');

        // Get workflow count by module
        $workflowCounts = ComVtigerWorkflow::selectRaw('module_name, COUNT(*) as count')
            ->groupBy('module_name')
            ->pluck('count', 'module_name');

        return view('tenant::settings.automation.workflows.index', compact(
            'workflows',
            'modules',
            'workflowCounts',
            'moduleFilter'
        ));
    }

    /**
     * Show the form for creating a new workflow
     */
    public function create(Request $request): View
    {
        $moduleName = $request->get('module');

        // Get available modules (you may want to filter this based on your system)
        $modules = $this->getAvailableModules();

        return view('tenant::settings.automation.workflows.create', compact('modules', 'moduleName'));
    }

    /**
     * Store a newly created workflow
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'module_name' => 'required|string|max:100',
            'workflowname' => 'required|string|max:255',
            'summary' => 'nullable|string',
            'execution_condition' => 'required|integer',
            'test' => 'nullable|string',
            'status' => 'boolean',
        ]);

        $workflow = ComVtigerWorkflow::create([
            'module_name' => $validated['module_name'],
            'summary' => $validated['summary'] ?? '',
            'test' => $validated['test'] ?? '[]',
            'execution_condition' => $validated['execution_condition'],
            'defaultworkflow' => 0,
            'type' => 'basic',
            'filtersavedinnew' => 6,
            'status' => $validated['status'] ?? 1,
            'workflowname' => $validated['workflowname'],
        ]);

        return redirect()
            ->route('tenant.settings.crm.automation.workflows.edit', $workflow->workflow_id)
            ->with('success', __('tenant::settings.workflow_created_successfully'));
    }

    /**
     * Show the form for editing the specified workflow
     */
    public function edit(int $id): View
    {
        $workflow = ComVtigerWorkflow::findOrFail($id);

        // Get tasks for this workflow
        $tasks = ComVtigerWorkflowtask::where('workflow_id', $id)->get();

        // Get execution conditions
        $executionConditions = $this->getExecutionConditions();

        // Get available modules
        $modules = $this->getAvailableModules();

        return view('tenant::settings.automation.workflows.edit', compact(
            'workflow',
            'tasks',
            'executionConditions',
            'modules'
        ));
    }

    /**
     * Update the specified workflow
     */
    public function update(Request $request, int $id)
    {
        $workflow = ComVtigerWorkflow::findOrFail($id);

        $validated = $request->validate([
            'workflowname' => 'required|string|max:255',
            'summary' => 'nullable|string',
            'execution_condition' => 'required|integer',
            'test' => 'nullable|string',
            'status' => 'boolean',
        ]);

        $workflow->update([
            'summary' => $validated['summary'] ?? '',
            'test' => $validated['test'] ?? '[]',
            'execution_condition' => $validated['execution_condition'],
            'status' => $validated['status'] ?? $workflow->status,
            'workflowname' => $validated['workflowname'],
        ]);

        return redirect()
            ->route('tenant.settings.crm.automation.workflows.index')
            ->with('success', __('tenant::settings.workflow_updated_successfully'));
    }

    /**
     * Remove the specified workflow
     */
    public function destroy(int $id)
    {
        $workflow = ComVtigerWorkflow::findOrFail($id);

        // Delete associated tasks
        ComVtigerWorkflowtask::where('workflow_id', $id)->delete();

        $workflow->delete();

        return redirect()
            ->route('tenant.settings.crm.automation.workflows.index')
            ->with('success', __('tenant::settings.workflow_deleted_successfully'));
    }

    /**
     * Toggle workflow status (AJAX)
     */
    public function toggleStatus(Request $request, int $id)
    {
        $workflow = ComVtigerWorkflow::findOrFail($id);

        $workflow->update([
            'status' => $request->input('status', 0)
        ]);

        return response()->json([
            'success' => true,
            'message' => __('tenant::settings.workflow_status_updated')
        ]);
    }

    /**
     * Get available modules for workflow creation
     */
    private function getAvailableModules(): array
    {
        // This should be fetched from your module registry
        // For now, returning common modules
        return [
            'Contacts' => 'Contacts',
            'Accounts' => 'Accounts',
            'Leads' => 'Leads',
            'Potentials' => 'Opportunities',
            'HelpDesk' => 'HelpDesk',
            'Quotes' => 'Quotes',
            'SalesOrder' => 'Sales Orders',
            'Invoice' => 'Invoices',
            'PurchaseOrder' => 'Purchase Orders',
            'Calendar' => 'Calendar',
        ];
    }

    /**
     * Get execution condition options
     */
    private function getExecutionConditions(): array
    {
        return [
            1 => __('tenant::settings.on_first_save'),
            2 => __('tenant::settings.once'),
            3 => __('tenant::settings.on_every_save'),
            4 => __('tenant::settings.on_modify'),
            6 => __('tenant::settings.on_schedule'),
            7 => __('tenant::settings.manual'),
        ];
    }
}
