<?php

declare(strict_types=1);

namespace App\Modules\Tenant\Settings\Presentation\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Tenant\ComVtigerWorkflow;
use App\Models\Tenant\ComVtigerWorkflowtask;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Http\JsonResponse;
use App\Modules\Core\VtigerModules\Contracts\ModuleRegistryInterface;

class WorkflowController extends Controller
{
    public function __construct(
        private ModuleRegistryInterface $moduleRegistry
    ) {
    }
    /**
     * Display a listing of workflows
     */
    public function index(Request $request): View
    {
        $activeModules = $this->moduleRegistry->getActive();
        $activeModuleNames = $activeModules->map(fn($m) => $m->getName())->toArray();

        // Get unique modules for filter (only active ones)
        $workflowModuleNames = ComVtigerWorkflow::select('module_name')
            ->whereIn('module_name', $activeModuleNames)
            ->distinct()
            ->orderBy('module_name')
            ->pluck('module_name');

        // Map names to labels
        $modules = [];
        foreach ($workflowModuleNames as $name) {
            $moduleDef = $activeModules->first(fn($m) => $m->getName() === $name);
            $modules[$name] = $moduleDef ? $moduleDef->getLabel() : $name;
        }

        // Get workflow count by module (only active ones)
        $workflowCounts = ComVtigerWorkflow::selectRaw('module_name, COUNT(*) as count')
            ->whereIn('module_name', $activeModuleNames)
            ->groupBy('module_name')
            ->pluck('count', 'module_name');

        return view('tenant::settings.automation.workflows.index', compact(
            'modules',
            'workflowCounts'
        ));
    }

    /**
     * Get data for DataTables (AJAX)
     */
    public function data(Request $request)
    {
        $activeModuleNames = $this->moduleRegistry->getActive()->map(fn($m) => $m->getName())->toArray();
        $moduleFilter = $request->get('module', 'all');

        $query = ComVtigerWorkflow::query()
            ->whereIn('module_name', $activeModuleNames);

        if ($moduleFilter !== 'all' && in_array($moduleFilter, $activeModuleNames)) {
            $query->where('module_name', $moduleFilter);
        }

        return \Yajra\DataTables\Facades\DataTables::eloquent($query)
            ->editColumn('status', function ($workflow) {
                $checked = $workflow->status ? 'checked' : '';
                return '
                    <div class="form-check form-switch d-flex justify-content-center">
                        <input class="form-check-input status-toggle" type="checkbox" role="switch"
                            data-workflow-id="' . $workflow->workflow_id . '" ' . $checked . '>
                    </div>';
            })
            ->editColumn('module_name', function ($workflow) {
                $label = $workflow->module_name;
                if ($this->moduleRegistry->has($workflow->module_name)) {
                    $label = $this->moduleRegistry->get($workflow->module_name)->getLabel();
                }
                return '<span class="badge bg-soft-info text-info rounded-pill px-3">' . $label . '</span>';
            })
            ->addColumn('actions', function ($workflow) {
                return '
                    <div class="d-flex justify-content-center gap-2">
                        <a href="' . route('tenant.settings.crm.automation.workflows.edit', $workflow->workflow_id) . '"
                            class="btn btn-sm btn-soft-primary rounded-2" title="' . __('tenant::settings.edit') . '">
                            <i class="bi bi-pencil"></i>
                        </a>
                        <button type="button" class="btn btn-sm btn-soft-danger rounded-2 delete-workflow"
                            data-workflow-id="' . $workflow->workflow_id . '" title="' . __('tenant::settings.delete') . '">
                            <i class="bi bi-trash"></i>
                        </button>
                    </div>';
            })
            ->rawColumns(['status', 'module_name', 'actions'])
            ->make(true);
    }

    /**
     * Show the form for creating a new workflow
     */
    public function create(Request $request): View
    {
        $moduleName = $request->get('module');

        // Get available modules
        $modules = $this->getAvailableModules();

        // Get execution conditions
        $executionConditions = $this->getExecutionConditions();

        // Get schedule types
        $scheduleTypes = $this->getScheduleTypes();

        // Get task types
        $taskTypes = $this->getTaskTypes();

        // Empty conditions array for new workflow
        $conditions = [];

        return view('tenant::settings.automation.workflows.create', compact(
            'modules',
            'moduleName',
            'executionConditions',
            'scheduleTypes',
            'taskTypes',
            'conditions'
        ));
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
            'status' => 'boolean',
            'conditions' => 'nullable|array',
            'schtypeid' => 'nullable|integer',
            'schtime' => 'nullable|string',
            'schdayofweek' => 'nullable|string',
            'schdayofmonth' => 'nullable|string',
            'schannualdates' => 'nullable|string',
            'schdayofweekexclude' => 'nullable|string',
            'timefrom' => 'nullable|string',
            'timeto' => 'nullable|string',
        ]);

        $conditions = $this->processConditions($request->input('conditions', []));

        $workflow = ComVtigerWorkflow::create([
            'module_name' => $validated['module_name'],
            'summary' => $validated['summary'] ?? '',
            'test' => json_encode($conditions),
            'execution_condition' => $validated['execution_condition'],
            'defaultworkflow' => 0,
            'type' => 'basic',
            'filtersavedinnew' => 6,
            'status' => $validated['status'] ?? 1,
            'workflowname' => $validated['workflowname'],
            'schtypeid' => $validated['schtypeid'] ?? null,
            'schtime' => $validated['schtime'] ?? null,
            'schdayofweek' => $validated['schdayofweek'] ?? null,
            'schdayofmonth' => $validated['schdayofmonth'] ?? null,
            'schannualdates' => $validated['schannualdates'] ?? null,
            'schdayofweekexclude' => $validated['schdayofweekexclude'] ?? null,
            'timefrom' => $validated['timefrom'] ?? null,
            'timeto' => $validated['timeto'] ?? null,
        ]);

        return redirect()
            ->route('tenant.settings.crm.automation.workflows.index')
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

        // Get schedule types
        $scheduleTypes = $this->getScheduleTypes();

        // Get task types
        $taskTypes = $this->getTaskTypes();

        // Get available modules
        $modules = $this->getAvailableModules();

        // Parse conditions if they exist
        $conditions = $workflow->test ? json_decode($workflow->test, true) : [];

        return view('tenant::settings.automation.workflows.edit', compact(
            'workflow',
            'tasks',
            'executionConditions',
            'scheduleTypes',
            'taskTypes',
            'modules',
            'conditions'
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
            'status' => 'boolean',
            'conditions' => 'nullable|array',
            'schtypeid' => 'nullable|integer',
            'schtime' => 'nullable|string',
            'schdayofweek' => 'nullable|string',
            'schdayofmonth' => 'nullable|string',
            'schannualdates' => 'nullable|string',
            'schdayofweekexclude' => 'nullable|string',
            'timefrom' => 'nullable|string',
            'timeto' => 'nullable|string',
        ]);

        $conditions = $this->processConditions($request->input('conditions', []));

        $workflow->update([
            'summary' => $validated['summary'] ?? '',
            'test' => json_encode($conditions),
            'execution_condition' => $validated['execution_condition'],
            'status' => $validated['status'] ?? $workflow->status,
            'workflowname' => $validated['workflowname'],
            'schtypeid' => $validated['schtypeid'] ?? $workflow->schtypeid,
            'schtime' => $validated['schtime'] ?? $workflow->schtime,
            'schdayofweek' => $validated['schdayofweek'] ?? $workflow->schdayofweek,
            'schdayofmonth' => $validated['schdayofmonth'] ?? $workflow->schdayofmonth,
            'schannualdates' => $validated['schannualdates'] ?? $workflow->schannualdates,
            'schdayofweekexclude' => $validated['schdayofweekexclude'] ?? $workflow->schdayofweekexclude,
            'timefrom' => $validated['timefrom'] ?? $workflow->timefrom,
            'timeto' => $validated['timeto'] ?? $workflow->timeto,
        ]);

        return redirect()
            ->route('tenant.settings.crm.automation.workflows.index')
            ->with('success', __('tenant::settings.workflow_updated_successfully'));
    }

    /**
     * Helper to process conditions array into Vtiger JSON format
     */
    private function processConditions(array $conditionsInput): array
    {
        $conditions = [];
        $index = 0;
        foreach ($conditionsInput as $condition) {
            if (!empty($condition['fieldname']) && !empty($condition['operation'])) {
                $groupType = $condition['group'] ?? 'all';
                $conditions[] = [
                    'fieldname' => $condition['fieldname'],
                    'operation' => $condition['operation'],
                    'value' => $condition['value'] ?? '',
                    'valuetype' => 'rawtext',
                    'joincondition' => ($groupType === 'all') ? 'and' : 'or',
                    'groupid' => ($groupType === 'all') ? 0 : 1,
                    'groupjoin' => ($index === 0) ? '' : 'and'
                ];
                $index++;
            }
        }
        return $conditions;
    }

    /**
     * Remove the specified workflow (AJAX compatible)
     */
    public function destroy(int $id): JsonResponse
    {
        $workflow = ComVtigerWorkflow::find($id);

        if (!$workflow) {
            return response()->json([
                'success' => false,
                'message' => __('tenant::settings.workflow_not_found') ?? 'Workflow not found.'
            ], 404);
        }

        try {
            // Delete associated tasks
            ComVtigerWorkflowtask::where('workflow_id', $id)->delete();

            $workflow->delete();

            return response()->json([
                'success' => true,
                'message' => __('tenant::settings.workflow_deleted_successfully')
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
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
        return $this->moduleRegistry->getActive()
            ->mapWithKeys(fn($m) => [$m->getName() => $m->getLabel()])
            ->toArray();
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

    /**
     * Get schedule type options
     */
    private function getScheduleTypes(): array
    {
        return [
            1 => __('tenant::settings.schedule_hourly'),
            2 => __('tenant::settings.schedule_daily'),
            3 => __('tenant::settings.schedule_weekly'),
            4 => __('tenant::settings.schedule_specific_date'),
            5 => __('tenant::settings.schedule_monthly_by_date'),
            6 => __('tenant::settings.schedule_annually'),
        ];
    }

    /**
     * Get available task types
     */
    private function getTaskTypes(): array
    {
        return [
            'VTEmailTask' => __('tenant::settings.task_send_email'),
            'VTUpdateFieldsTask' => __('tenant::settings.task_update_fields'),
            'VTCreateEntityTask' => __('tenant::settings.task_create_entity'),
            'VTCreateTodoTask' => __('tenant::settings.task_create_todo'),
            'VTCreateEventTask' => __('tenant::settings.task_create_event'),
            'VTSMSTask' => __('tenant::settings.task_send_sms'),
            'VTSendNotificationTask' => __('tenant::settings.task_push_notification'),
        ];
    }

    /**
     * Get module fields for condition builder (AJAX)
     */
    public function getModuleFields(Request $request)
    {
        $moduleName = $request->get('module');

        if (!$moduleName) {
            return response()->json(['error' => 'Module name required'], 400);
        }

        // Get fields from vtiger_field table
        $fields = \DB::table('vtiger_field')
            ->where('tabid', function ($query) use ($moduleName) {
                $query->select('tabid')
                    ->from('vtiger_tab')
                    ->where('name', $moduleName)
                    ->limit(1);
            })
            ->where('presence', '!=', 1) // Exclude hidden fields
            ->orderBy('block')
            ->orderBy('sequence')
            ->get(['fieldname', 'fieldlabel', 'uitype', 'typeofdata'])
            ->map(function ($field) {
                return [
                    'name' => $field->fieldname,
                    'label' => $field->fieldlabel,
                    'type' => $this->getFieldType($field->uitype),
                    'uitype' => $field->uitype,
                ];
            });

        return response()->json(['fields' => $fields]);
    }

    /**
     * Get field type based on uitype
     */
    private function getFieldType($uitype): string
    {
        $typeMap = [
            1 => 'text',
            2 => 'text',
            7 => 'number',
            9 => 'number',
            10 => 'reference',
            13 => 'email',
            14 => 'time',
            15 => 'picklist',
            16 => 'picklist',
            17 => 'url',
            19 => 'textarea',
            21 => 'textarea',
            23 => 'date',
            24 => 'textarea',
            33 => 'multi-picklist',
            50 => 'date',
            51 => 'reference',
            52 => 'reference',
            53 => 'reference',
            56 => 'boolean',
            57 => 'reference',
            58 => 'reference',
            59 => 'reference',
            66 => 'reference',
            68 => 'reference',
            71 => 'currency',
            72 => 'currency',
            73 => 'reference',
            75 => 'reference',
            76 => 'reference',
            77 => 'reference',
            78 => 'reference',
            80 => 'reference',
            81 => 'reference',
            101 => 'reference',
            117 => 'reference',
        ];

        return $typeMap[$uitype] ?? 'text';
    }

    /**
     * Get condition operators
     */
    public function getConditionOperators()
    {
        return response()->json([
            'operators' => [
                'is' => __('tenant::settings.operator_is'),
                'is not' => __('tenant::settings.operator_is_not'),
                'contains' => __('tenant::settings.operator_contains'),
                'does not contain' => __('tenant::settings.operator_does_not_contain'),
                'starts with' => __('tenant::settings.operator_starts_with'),
                'ends with' => __('tenant::settings.operator_ends_with'),
                'less than' => __('tenant::settings.operator_less_than'),
                'greater than' => __('tenant::settings.operator_greater_than'),
                'less than or equal' => __('tenant::settings.operator_less_than_or_equal'),
                'greater than or equal' => __('tenant::settings.operator_greater_than_or_equal'),
                'is empty' => __('tenant::settings.operator_is_empty'),
                'is not empty' => __('tenant::settings.operator_is_not_empty'),
                'before' => __('tenant::settings.operator_before'),
                'after' => __('tenant::settings.operator_after'),
                'between' => __('tenant::settings.operator_between'),
                'has changed' => __('tenant::settings.operator_has_changed'),
                'has changed to' => __('tenant::settings.operator_has_changed_to'),
            ]
        ]);
    }

    /**
     * Update workflow conditions (AJAX)
     */
    public function updateConditions(Request $request, int $id)
    {
        $workflow = ComVtigerWorkflow::findOrFail($id);

        $validated = $request->validate([
            'conditions' => 'required|json',
        ]);

        $workflow->update([
            'test' => $validated['conditions']
        ]);

        return response()->json([
            'success' => true,
            'message' => __('tenant::settings.conditions_updated_successfully')
        ]);
    }

    /**
     * Create a new task for workflow
     */
    public function createTask(Request $request, int $workflowId)
    {
        $workflow = ComVtigerWorkflow::findOrFail($workflowId);

        $validated = $request->validate([
            'task_type' => 'required|string',
            'summary' => 'required|string|max:400',
            'task_data' => 'required|array',
        ]);

        $task = ComVtigerWorkflowtask::create([
            'workflow_id' => $workflowId,
            'summary' => $validated['summary'],
            'task' => serialize($validated['task_data']),
        ]);

        return response()->json([
            'success' => true,
            'message' => __('tenant::settings.task_created_successfully'),
            'task' => $task
        ]);
    }

    /**
     * Update a workflow task
     */
    public function updateTask(Request $request, int $workflowId, int $taskId)
    {
        $task = ComVtigerWorkflowtask::where('workflow_id', $workflowId)
            ->where('task_id', $taskId)
            ->firstOrFail();

        $validated = $request->validate([
            'summary' => 'required|string|max:400',
            'task_data' => 'required|array',
        ]);

        $task->update([
            'summary' => $validated['summary'],
            'task' => serialize($validated['task_data']),
        ]);

        return response()->json([
            'success' => true,
            'message' => __('tenant::settings.task_updated_successfully')
        ]);
    }

    /**
     * Delete a workflow task
     */
    public function deleteTask(int $workflowId, int $taskId)
    {
        $task = ComVtigerWorkflowtask::where('workflow_id', $workflowId)
            ->where('task_id', $taskId)
            ->firstOrFail();

        $task->delete();

        return response()->json([
            'success' => true,
            'message' => __('tenant::settings.task_deleted_successfully')
        ]);
    }

    /**
     * Update workflow schedule configuration
     */
    public function updateSchedule(Request $request, int $id)
    {
        $workflow = ComVtigerWorkflow::findOrFail($id);

        $validated = $request->validate([
            'schtypeid' => 'required|integer|min:1|max:7',
            'schtime' => 'nullable|date_format:H:i',
            'schdayofweek' => 'nullable|json',
            'schdayofmonth' => 'nullable|json',
            'schannualdates' => 'nullable|json',
        ]);

        $workflow->update($validated);

        return response()->json([
            'success' => true,
            'message' => __('tenant::settings.schedule_updated_successfully')
        ]);
    }
}
