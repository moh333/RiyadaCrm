<?php

namespace App\Modules\Tenant\Contacts\Presentation\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Tenant\Contacts\Domain\Repositories\ContactRepositoryInterface;
use App\Modules\Tenant\Contacts\Application\UseCases\CreateContactUseCase;
use App\Modules\Tenant\Contacts\Application\UseCases\UpdateContactUseCase;
use App\Modules\Tenant\Contacts\Application\UseCases\DeleteContactUseCase;
use App\Modules\Tenant\Contacts\Application\UseCases\CreateContactDTO;
use App\Modules\Tenant\Contacts\Application\UseCases\UpdateContactDTO;
use Illuminate\Http\Request;

class ContactsController extends Controller
{
    private ContactRepositoryInterface $contactRepository;
    private CreateContactUseCase $createContactUseCase;
    private UpdateContactUseCase $updateContactUseCase;
    private DeleteContactUseCase $deleteContactUseCase;
    private \App\Modules\Tenant\Contacts\Application\UseCases\GetModuleCustomFieldsUseCase $getModuleCustomFieldsUseCase;

    public function __construct(
        ContactRepositoryInterface $contactRepository,
        CreateContactUseCase $createContactUseCase,
        UpdateContactUseCase $updateContactUseCase,
        DeleteContactUseCase $deleteContactUseCase,
        \App\Modules\Tenant\Contacts\Application\UseCases\GetModuleCustomFieldsUseCase $getModuleCustomFieldsUseCase,
        private \App\Modules\Core\VtigerModules\Contracts\ModuleRegistryInterface $moduleRegistry
    ) {
        $this->contactRepository = $contactRepository;
        $this->createContactUseCase = $createContactUseCase;
        $this->updateContactUseCase = $updateContactUseCase;
        $this->deleteContactUseCase = $deleteContactUseCase;
        $this->getModuleCustomFieldsUseCase = $getModuleCustomFieldsUseCase;
    }

    public function index(Request $request)
    {
        return view('contacts_module::contacts.index');
    }

    public function data()
    {
        $query = $this->contactRepository->getDataTableQuery();

        return \Yajra\DataTables\Facades\DataTables::query($query)
            ->addColumn('full_name', function ($row) {
                $displayName = trim(($row->salutation ? $row->salutation . ' ' : '') . $row->firstname . ' ' . $row->lastname);
                $viewUrl = route('tenant.contacts.show', $row->contactid);
                $avatar = "https://ui-avatars.com/api/?name=" . urlencode($displayName) . "&background=6366f1&color=fff";

                return '
                    <div class="d-flex align-items-center">
                        <img src="' . $avatar . '" class="rounded-circle me-3" width="36" height="36" alt="">
                        <div>
                            <a href="' . $viewUrl . '" class="text-decoration-none fw-bold text-main d-block">
                                ' . $displayName . '
                            </a>
                            ' . ($row->title ? '<small class="text-muted">' . e($row->title) . '</small>' : '') . '
                        </div>
                    </div>';
            })
            ->editColumn('contact_no', function ($row) {
                return '<span class="badge bg-soft-primary text-primary rounded-pill px-3">' . $row->contact_no . '</span>';
            })
            ->editColumn('email', function ($row) {
                if ($row->email) {
                    return '<a href="mailto:' . e($row->email) . '" class="text-muted text-decoration-none">
                                <i class="bi bi-envelope me-1"></i> ' . e($row->email) . '
                            </a>';
                }
                return '<span class="text-muted">-</span>';
            })
            ->editColumn('account_name', function ($row) {
                if ($row->account_name) {
                    return '<span class="text-main"><i class="bi bi-building me-1"></i> ' . e($row->account_name) . '</span>';
                }
                return '<span class="text-muted">-</span>';
            })
            ->addColumn('actions', function ($row) {
                $viewUrl = route('tenant.contacts.show', $row->contactid);
                $editUrl = route('tenant.contacts.edit', $row->contactid);
                $deleteUrl = route('tenant.contacts.destroy', $row->contactid);
                $confirmMsg = __('contacts::contacts.are_you_sure');

                return '
                    <div class="d-flex justify-content-end gap-2">
                        <a href="' . $viewUrl . '" class="btn btn-sm btn-soft-info rounded-2" title="' . __('contacts::contacts.view') . '">
                            <i class="bi bi-eye"></i>
                        </a>
                        <a href="' . $editUrl . '" class="btn btn-sm btn-soft-primary rounded-2" title="' . __('contacts::contacts.edit') . '">
                            <i class="bi bi-pencil"></i>
                        </a>
                        <form action="' . $deleteUrl . '" method="POST" onsubmit="return confirm(\'' . $confirmMsg . '\')" class="d-inline">
                            ' . csrf_field() . '
                            ' . method_field('DELETE') . '
                            <button type="submit" class="btn btn-sm btn-soft-danger rounded-2" title="' . __('contacts::contacts.delete') . '">
                                <i class="bi bi-trash"></i>
                            </button>
                        </form>
                    </div>';
            })
            ->rawColumns(['full_name', 'contact_no', 'email', 'account_name', 'actions'])
            ->make(true);
    }

    public function show($id)
    {
        $contact = $this->contactRepository->findById((int) $id);

        if (!$contact) {
            abort(404);
        }

        $module = $this->moduleRegistry->get('Contacts');
        return view('contacts_module::contacts.show', compact('contact', 'module'));

    }

    public function create()
    {
        $module = $this->moduleRegistry->get('Contacts');
        return view('contacts_module::contacts.create', compact('module'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'lastname' => 'required|string|max:80',
            'firstname' => 'nullable|string|max:40',
            'salutation' => 'nullable|string|max:200',
            'email' => 'nullable|email|max:100',
            'account_id' => 'nullable|integer',
            'phone' => 'nullable|string|max:50',
            'mobile' => 'nullable|string|max:50',
            'title' => 'nullable|string|max:50',
            'department' => 'nullable|string|max:30',
        ]);

        // Extract fields
        $customFields = [];
        $imagePath = null;

        // Handle standard/custom inputs
        foreach ($request->input() as $key => $value) {
            if (str_starts_with($key, 'cf_')) {
                $customFields[$key] = $value;
            }
        }

        // Handle file uploads
        foreach ($request->allFiles() as $key => $file) {
            $path = $file->store('custom_fields', 'public');
            if (str_starts_with($key, 'cf_')) {
                $customFields[$key] = $path;
            } elseif ($key === 'imagename') {
                $imagePath = $path;
            }
        }

        $dto = new CreateContactDTO([
            'lastName' => $validated['lastname'],
            'firstName' => $validated['firstname'] ?? null,
            'salutation' => $validated['salutation'] ?? null,
            'email' => $validated['email'] ?? null,
            'accountId' => $validated['account_id'] ?? null,
            'ownerId' => auth('tenant')->id(),
            'creatorId' => auth('tenant')->id(),
            'phone' => $validated['phone'] ?? null,
            'mobile' => $validated['mobile'] ?? null,
            'title' => $validated['title'] ?? null,
            'department' => $validated['department'] ?? null,
            'image' => $imagePath,
            'customFields' => $customFields,
        ]);

        $contact = $this->createContactUseCase->execute($dto);

        return redirect()->route('tenant.contacts.show', $contact->getId())
            ->with('success', __('contacts::contacts.created_successfully'));

    }

    public function edit($id)
    {
        $contact = $this->contactRepository->findById((int) $id);

        if (!$contact) {
            abort(404);
        }

        $module = $this->moduleRegistry->get('Contacts');
        return view('contacts_module::contacts.edit', compact('contact', 'module'));
    }

    public function update(Request $request, $id)
    {
        $validated = $request->validate([
            'lastname' => 'required|string|max:80',
            'firstname' => 'nullable|string|max:40',
            'salutation' => 'nullable|string|max:200',
            'email' => 'nullable|email|max:100',
            'account_id' => 'nullable|integer',
            'phone' => 'nullable|string|max:50',
            'mobile' => 'nullable|string|max:50',
            'title' => 'nullable|string|max:50',
            'department' => 'nullable|string|max:30',
            'description' => 'nullable|string',
        ]);

        // Extract fields
        $customFields = [];
        $imagePath = null;

        // Handle standard/custom inputs
        foreach ($request->input() as $key => $value) {
            if (str_starts_with($key, 'cf_')) {
                $customFields[$key] = $value;
            }
        }

        // Handle file uploads
        foreach ($request->allFiles() as $key => $file) {
            $path = $file->store('custom_fields', 'public');
            if (str_starts_with($key, 'cf_')) {
                $customFields[$key] = $path;
            } elseif ($key === 'imagename') {
                $imagePath = $path;
            }
        }

        // Get existing contact to preserve image if not uploading new one
        $existingContact = $this->contactRepository->findById((int) $id);

        // If no new image uploaded, keep the existing one
        if ($imagePath === null && $existingContact) {
            $imagePath = $existingContact->getImageName();
        }

        $dto = new UpdateContactDTO([
            'lastName' => $validated['lastname'],
            'firstName' => $validated['firstname'] ?? null,
            'salutation' => $validated['salutation'] ?? null,
            'email' => $validated['email'] ?? null,
            'accountId' => $validated['account_id'] ?? null,
            'modifiedBy' => auth('tenant')->id(),
            'phone' => $validated['phone'] ?? null,
            'mobile' => $validated['mobile'] ?? null,
            'title' => $validated['title'] ?? null,
            'department' => $validated['department'] ?? null,
            'description' => $validated['description'] ?? null,
            'image' => $imagePath,
            'customFields' => $customFields,
        ]);


        $contact = $this->updateContactUseCase->execute((int) $id, $dto);

        return redirect()->route('tenant.contacts.show', $contact->getId())
            ->with('success', __('contacts::contacts.updated_successfully'));

    }

    public function destroy($id)
    {
        $this->deleteContactUseCase->execute((int) $id);

        return redirect()->route('tenant.contacts.index')
            ->with('success', __('contacts::contacts.deleted_successfully'));

    }
}
