<?php

declare(strict_types=1);

namespace App\Modules\Tenant\Settings\Presentation\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Tenant\VtigerCronTask;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Http\JsonResponse;
use App\Modules\Core\VtigerModules\Contracts\ModuleRegistryInterface;
use Yajra\DataTables\Facades\DataTables;

class SchedulerController extends Controller
{
    public function __construct(
        private ModuleRegistryInterface $moduleRegistry
    ) {
    }
    /**
     * Display a listing of scheduled tasks
     */
    public function index(): View
    {
        $query = VtigerCronTask::query();

        // Get statistics
        $stats = [
            'total' => $query->count(),
            'active' => $query->clone()->where('status', 1)->count(),
            'disabled' => $query->clone()->where('status', 0)->count(),
            'running' => $query->clone()->get()->filter(function ($task) {
                return $task->isRunning();
            })->count(),
        ];

        return view('tenant::settings.automation.scheduler.index', compact('stats'));
    }

    /**
     * Get data for DataTables (AJAX)
     */
    public function data()
    {
        $query = VtigerCronTask::query();

        return DataTables::eloquent($query)
            ->editColumn('status', function ($task) {
                $checked = $task->status == 1 ? 'checked' : '';
                return '
                    <div class="form-check form-switch">
                        <input class="form-check-input status-toggle" type="checkbox" 
                            data-task-id="' . $task->id . '" ' . $checked . '>
                    </div>';
            })
            ->editColumn('laststart', function ($task) {
                return $task->last_run ?: '<span class="text-muted">' . __('tenant::settings.never') . '</span>';
            })
            ->editColumn('lastend', function ($task) {
                return $task->last_end_time ?: '<span class="text-muted">' . __('tenant::settings.never') . '</span>';
            })
            ->editColumn('module', function ($task) {
                if (!$task->module) {
                    return '<span class="text-muted">-</span>';
                }

                $label = $task->module;
                if ($this->moduleRegistry->has($task->module)) {
                    $label = $this->moduleRegistry->get($task->module)->getLabel();
                }

                return '<span class="badge bg-soft-info text-info rounded-pill px-3">' . $label . '</span>';
            })
            ->addColumn('frequency_label', function ($task) {
                return $task->frequency_label;
            })
            ->addColumn('actions', function ($task) {
                return '
                    <div class="d-flex gap-2">
                        <button type="button" class="btn btn-outline-primary btn-sm run-task-btn"
                            data-task-id="' . $task->id . '" title="' . __('tenant::settings.run_now') . '">
                            <i class="bi bi-play-fill text-primary"></i>
                        </button>
                        <a href="' . route('tenant.settings.crm.automation.scheduler.edit', $task->id) . '"
                            class="btn btn-outline-secondary btn-sm" title="' . __('tenant::settings.edit') . '">
                            <i class="bi bi-pencil"></i>
                        </a>
                        <button type="button" class="btn btn-outline-danger btn-sm delete-task-btn"
                            data-task-id="' . $task->id . '" title="' . __('tenant::settings.delete') . '">
                            <i class="bi bi-trash"></i>
                        </button>
                    </div>';
            })
            ->rawColumns(['status', 'laststart', 'lastend','module', 'actions'])
            ->make(true);
    }

    /**
     * Show the form for creating a new scheduled task
     */
    public function create(): View
    {
        $modules = $this->getAvailableModules();
        return view('tenant::settings.automation.scheduler.create', compact('modules'));
    }

    /**
     * Store a newly created scheduled task
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'handler_file' => 'required|string|max:255',
            'frequency' => 'required|integer|min:60',
            'status' => 'boolean',
            'module' => 'nullable|string|max:100',
            'description' => 'nullable|string',
        ]);

        // Get next sequence
        $maxSequence = VtigerCronTask::max('sequence') ?: 0;

        VtigerCronTask::create([
            'name' => $validated['name'],
            'handler_file' => $validated['handler_file'],
            'frequency' => $validated['frequency'],
            'status' => $validated['status'] ?? 1,
            'module' => $validated['module'],
            'description' => $validated['description'],
            'sequence' => $maxSequence + 1,
        ]);

        return redirect()
            ->route('tenant.settings.crm.automation.scheduler.index')
            ->with('success', __('tenant::settings.scheduler_created_successfully'));
    }

    /**
     * Show the form for editing a cron task
     */
    public function edit(int $id): View
    {
        $cronTask = VtigerCronTask::findOrFail($id);
        $modules = $this->getAvailableModules();

        return view('tenant::settings.automation.scheduler.edit', compact('cronTask', 'modules'));
    }

    /**
     * Update the specified cron task
     */
    public function update(Request $request, int $id)
    {
        $cronTask = VtigerCronTask::findOrFail($id);

        $validated = $request->validate([
            'frequency' => 'required|integer|min:60',
            'status' => 'boolean',
        ]);

        $cronTask->update([
            'frequency' => $validated['frequency'],
            'status' => $validated['status'] ?? $cronTask->status,
        ]);

        return redirect()
            ->route('tenant.settings.crm.automation.scheduler.index')
            ->with('success', __('tenant::settings.scheduler_updated_successfully'));
    }

    /**
     * Toggle cron task status (AJAX)
     */
    public function toggleStatus(Request $request, int $id): JsonResponse
    {
        $cronTask = VtigerCronTask::findOrFail($id);

        $cronTask->update([
            'status' => $request->input('status', 0)
        ]);

        return response()->json([
            'success' => true,
            'message' => __('tenant::settings.scheduler_status_updated')
        ]);
    }

    /**
     * Run a cron task manually (AJAX)
     */
    public function runNow(int $id): JsonResponse
    {
        $cronTask = VtigerCronTask::findOrFail($id);

        // Check if task is already running
        if ($cronTask->isRunning()) {
            return response()->json([
                'success' => false,
                'message' => __('tenant::settings.scheduler_already_running')
            ], 422);
        }

        // Update laststart time
        $cronTask->update([
            'laststart' => time()
        ]);

        // In a real implementation, you would trigger the actual cron job here
        // For now, we'll just simulate completion
        $cronTask->update([
            'lastend' => time()
        ]);

        return response()->json([
            'success' => true,
            'message' => __('tenant::settings.scheduler_run_successfully'),
            'last_run' => $cronTask->last_run,
        ]);
    }

    /**
     * Get cron task details (AJAX)
     */
    public function getDetails(int $id): JsonResponse
    {
        $cronTask = VtigerCronTask::findOrFail($id);

        return response()->json([
            'success' => true,
            'task' => [
                'id' => $cronTask->id,
                'name' => $cronTask->name,
                'description' => $cronTask->description,
                'frequency' => $cronTask->frequency,
                'frequency_label' => $cronTask->frequency_label,
                'status' => $cronTask->status,
                'status_label' => $cronTask->status_label,
                'last_run' => $cronTask->last_run,
                'last_end' => $cronTask->last_end_time,
                'is_running' => $cronTask->isRunning(),
                'module' => $cronTask->module,
            ]
        ]);
    }

    /**
     * Remove the specified cron task
     */
    public function destroy(int $id): JsonResponse
    {
        $cronTask = VtigerCronTask::findOrFail($id);
        $cronTask->delete();

        return response()->json([
            'success' => true,
            'message' => __('tenant::settings.scheduler_deleted_successfully')
        ]);
    }

    /**
     * Get available modules
     */
    private function getAvailableModules(): array
    {
        return $this->moduleRegistry->getActive()
            ->mapWithKeys(fn($m) => [$m->getName() => $m->getLabel()])
            ->toArray();
    }
}
