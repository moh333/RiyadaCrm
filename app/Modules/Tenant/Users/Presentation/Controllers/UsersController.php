<?php

namespace App\Modules\Tenant\Users\Presentation\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Tenant\Users\Application\UseCases\CreateUserDTO;
use App\Modules\Tenant\Users\Application\UseCases\CreateUserUseCase;
use App\Modules\Tenant\Users\Application\UseCases\UpdateUserDTO;
use App\Modules\Tenant\Users\Application\UseCases\UpdateUserUseCase;
use App\Modules\Tenant\Users\Domain\Repositories\UserRepositoryInterface;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class UsersController extends Controller
{
    public function __construct(
        private UserRepositoryInterface $repository,
        private CreateUserUseCase $createUseCase,
        private UpdateUserUseCase $updateUseCase
    ) {
    }

    public function index(Request $request)
    {
        if ($request->ajax()) {
            $query = $this->repository->getDataTableQuery();

            return \Yajra\DataTables\Facades\DataTables::query($query)
                ->addColumn('full_name', function ($row) {
                    $fullName = trim($row->first_name . ' ' . $row->last_name);
                    $displayName = $fullName ?: $row->user_name;
                    $avatar = "https://ui-avatars.com/api/?name=" . urlencode($displayName) . "&background=6366f1&color=fff";

                    return '
                        <div class="d-flex align-items-center">
                            <div class="avatar-sm me-3 bg-soft-primary text-primary rounded-circle d-flex align-items-center justify-content-center fw-bold overflow-hidden">
                                <img src="' . $avatar . '" width="36" height="36" alt="">
                            </div>
                            <div>
                                <h6 class="mb-0 fw-bold">' . e($displayName) . '</h6>
                                ' . ($row->title ? '<small class="text-muted">' . e($row->title) . '</small>' : '') . '
                            </div>
                        </div>';
                })
                ->addColumn('rolename', function ($row) {
                    return '<span class="badge bg-light text-dark border">' . e($row->rolename ?? '-') . '</span>';
                })
                ->editColumn('email1', function ($row) {
                    return '<a href="mailto:' . e($row->email1) . '" class="text-muted text-decoration-none">
                                <i class="bi bi-envelope me-1"></i> ' . e($row->email1) . '
                            </a>';
                })
                ->editColumn('status', function ($row) {
                    if ($row->status === 'Active') {
                        return '<span class="badge bg-soft-success text-success">' . (__('tenant::users.active') ?? 'Active') . '</span>';
                    }
                    return '<span class="badge bg-soft-danger text-danger">' . (__('tenant::users.inactive') ?? 'Inactive') . '</span>';
                })
                ->addColumn('actions', function ($row) {
                    $editUrl = route('tenant.settings.users.edit', $row->id);
                    $deleteUrl = route('tenant.settings.users.destroy', $row->id);
                    $confirmMsg = __('tenant::users.are_you_sure') ?? 'Are you sure?';
                    $canDelete = auth('tenant')->id() !== $row->id;

                    $deleteBtn = '';
                    if ($canDelete) {
                        $deleteBtn = '
                        <form action="' . $deleteUrl . '" method="POST" onsubmit="return confirm(\'' . $confirmMsg . '\')" class="d-inline">
                            ' . csrf_field() . '
                            ' . method_field('DELETE') . '
                            <button type="submit" class="btn btn-sm btn-outline-danger rounded-3" title="' . (__('tenant::users.delete') ?? 'Delete') . '">
                                <i class="bi bi-trash"></i>
                            </button>
                        </form>';
                    }

                    return '
                        <div class="d-flex justify-content-end gap-2">
                            <a href="' . $editUrl . '" class="btn btn-sm btn-outline-secondary rounded-3" title="' . (__('tenant::users.edit') ?? 'Edit') . '">
                                <i class="bi bi-pencil"></i>
                            </a>
                            ' . $deleteBtn . '
                        </div>';
                })
                ->rawColumns(['full_name', 'rolename', 'email1', 'status', 'actions'])
                ->make(true);
        }

        return view('tenant::users.index');
    }

    public function create()
    {
        $roles = DB::connection('tenant')->table('vtiger_role')->orderBy('rolename')->get();
        // Fetch users for 'Reports To', excluding deleted
        $users = DB::connection('tenant')->table('vtiger_users')
            ->where('deleted', 0)
            ->where('status', 'Active')
            ->select('id', 'first_name', 'last_name', 'user_name')
            ->get();

        return view('tenant::users.create', compact('roles', 'users'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'user_name' => 'required|string|max:255|unique:tenant.vtiger_users,user_name',
            'first_name' => 'nullable|string|max:255',
            'last_name' => 'required|string|max:255',
            'email1' => 'required|email|max:255|unique:tenant.vtiger_users,email1',
            'user_password' => 'required|string|min:6|confirmed',
            'roleid' => 'required|string',
            'status' => 'required|in:Active,Inactive',
            'is_admin' => 'boolean',
            'title' => 'nullable|string|max:100',
            'department' => 'nullable|string|max:100',
            'phone_mobile' => 'nullable|string|max:50',
            'phone_work' => 'nullable|string|max:50',
            'reports_to_id' => 'nullable|integer',
            'signature' => 'nullable|string',
            'address_street' => 'nullable|string',
            'address_city' => 'nullable|string',
            'address_state' => 'nullable|string',
            'address_postalcode' => 'nullable|string',
            'address_country' => 'nullable|string',
        ]);

        $dto = new CreateUserDTO(
            $validated['user_name'],
            $validated['first_name'] ?? '',
            $validated['last_name'],
            $validated['email1'],
            $validated['user_password'],
            $validated['roleid'],
            $validated['status'],
            $request->has('is_admin'), // Checkbox handling
            $validated['title'] ?? null,
            $validated['department'] ?? null,
            $validated['phone_mobile'] ?? null,
            $validated['phone_work'] ?? null,
            $validated['reports_to_id'] ?? null,
            $validated['signature'] ?? null,
            $validated['address_street'] ?? null,
            $validated['address_city'] ?? null,
            $validated['address_state'] ?? null,
            $validated['address_postalcode'] ?? null,
            $validated['address_country'] ?? null
        );

        $this->createUseCase->execute($dto);

        return redirect()->route('tenant.settings.users.index')
            ->with('success', __('tenant::users.created_successfully') ?? 'User created successfully.');
    }

    public function edit(int $id)
    {
        $user = $this->repository->findById($id);
        if (!$user)
            abort(404);

        $roles = DB::connection('tenant')->table('vtiger_role')->orderBy('rolename')->get();
        // Exclude self from reports to to avoid infinite recursion (though UI is enough usually)
        $users = DB::connection('tenant')->table('vtiger_users')
            ->where('deleted', 0)
            ->where('status', 'Active')
            ->where('id', '!=', $id)
            ->select('id', 'first_name', 'last_name', 'user_name')
            ->get();

        return view('tenant::users.edit', compact('user', 'roles', 'users'));
    }

    public function update(Request $request, int $id)
    {
        $user = $this->repository->findById($id);
        if (!$user)
            abort(404);

        $validated = $request->validate([
            'user_name' => 'required|string|max:255|unique:tenant.vtiger_users,user_name,' . $id,
            'first_name' => 'nullable|string|max:255',
            'last_name' => 'required|string|max:255',
            'email1' => 'required|email|max:255|unique:tenant.vtiger_users,email1,' . $id,
            'user_password' => 'nullable|string|min:6|confirmed',
            'roleid' => 'required|string',
            'status' => 'required|in:Active,Inactive',
            // is_admin: usually we don't strictly validate boolean presence for checkboxes as they're missing if unchecked
            'title' => 'nullable|string|max:100',
            'department' => 'nullable|string|max:100',
            'phone_mobile' => 'nullable|string|max:50',
            'phone_work' => 'nullable|string|max:50',
            'reports_to_id' => 'nullable|integer',
            'signature' => 'nullable|string',
            'address_street' => 'nullable|string',
            'address_city' => 'nullable|string',
            'address_state' => 'nullable|string',
            'address_postalcode' => 'nullable|string',
            'address_country' => 'nullable|string',
        ]);

        $dto = new UpdateUserDTO(
            $validated['user_name'],
            $validated['first_name'],
            $validated['last_name'],
            $validated['email1'],
            $validated['user_password'] ?? null,
            $validated['roleid'],
            $validated['status'],
            $request->has('is_admin'),
            $validated['title'],
            $validated['department'],
            $validated['phone_mobile'],
            $validated['phone_work'] ?? null,
            $validated['reports_to_id'] ?? null,
            $validated['signature'] ?? null,
            $validated['address_street'] ?? null,
            $validated['address_city'] ?? null,
            $validated['address_state'] ?? null,
            $validated['address_postalcode'] ?? null,
            $validated['address_country'] ?? null
        );

        $this->updateUseCase->execute($id, $dto);

        return redirect()->route('tenant.settings.users.index')
            ->with('success', __('tenant::users.updated_successfully') ?? 'User updated successfully.');
    }

    public function destroy(int $id)
    {
        if ($id === auth('tenant')->id()) {
            return back()->with('error', 'Cannot delete your own account.');
        }

        $this->repository->delete($id);
        return back()->with('success', __('tenant::users.deleted_successfully') ?? 'User deleted successfully.');
    }
}
