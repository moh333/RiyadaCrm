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
                $avatar = $row->imagename ? url('tenancy/assets/' . $row->imagename) : "https://ui-avatars.com/api/?name=" . urlencode($displayName) . "&background=6366f1&color=fff";
                $imgStyle = $row->imagename ? 'style="object-fit: cover;"' : '';

                return '
                    <div class="d-flex align-items-center">
                        <img src="' . $avatar . '" class="rounded-circle me-3" width="36" height="36" ' . $imgStyle . ' alt="">
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
            'salutationtype' => 'nullable|string|max:200',
            'email' => 'nullable|email|max:100',
            'account_id' => 'nullable|integer',
            'phone' => 'nullable|string|regex:/^\+?[1-9]\d{1,14}$/',
            'mobile' => 'nullable|string|regex:/^\+?[1-9]\d{1,14}$/',
            'homephone' => 'nullable|string|regex:/^\+?[1-9]\d{1,14}$/',
            'fax' => 'nullable|string|regex:/^\+?[1-9]\d{1,14}$/',
            'title' => 'nullable|string|max:50',
            'department' => 'nullable|string|max:30',
            'description' => 'nullable|string',
            'assistant' => 'nullable|string|max:30',
            'assistantphone' => 'nullable|string|regex:/^\+?[1-9]\d{1,14}$/',
            'birthday' => 'nullable|date',
            'leadsource' => 'nullable|string|max:200',
            'mailingstreet' => 'nullable|string',
            'mailingcity' => 'nullable|string|max:40',
            'mailingstate' => 'nullable|string|max:30',
            'mailingzip' => 'nullable|string|max:30',
            'mailingcountry' => 'nullable|string|max:30',
            'mailingpobox' => 'nullable|string|max:30',
            'otherstreet' => 'nullable|string',
            'othercity' => 'nullable|string|max:40',
            'otherstate' => 'nullable|string|max:30',
            'otherzip' => 'nullable|string|max:30',
            'othercountry' => 'nullable|string|max:30',
            'otherpobox' => 'nullable|string|max:30',
        ]);

        $salutation = $validated['salutation'] ?? $validated['salutationtype'] ?? null;

        // Get module definition for field validation
        $module = $this->moduleRegistry->get('Contacts');

        // Validate file uploads against field configuration
        $this->validateFileUploads($request, $module);

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
            // Handle multiple files
            if (is_array($file)) {
                $paths = [];
                foreach ($file as $singleFile) {
                    $paths[] = $singleFile->store('custom_fields', 'public');
                }
                if (str_starts_with($key, 'cf_')) {
                    $customFields[$key] = json_encode($paths); // Store as JSON array
                }
            } else {
                $path = $file->store('custom_fields', 'public');
                if (str_starts_with($key, 'cf_')) {
                    $customFields[$key] = $path;
                } elseif ($key === 'imagename') {
                    $imagePath = $path;
                }
            }
        }

        $dto = new CreateContactDTO(array_merge($validated, [
            'lastName' => $validated['lastname'],
            'firstName' => $validated['firstname'] ?? null,
            'salutation' => $salutation,
            'email' => $validated['email'] ?? null,
            'accountId' => $validated['account_id'] ?? null,
            'ownerId' => auth('tenant')->id(),
            'creatorId' => auth('tenant')->id(),
            'image' => $imagePath,
            'customFields' => $customFields,
        ]));

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
            'salutationtype' => 'nullable|string|max:200',
            'email' => 'nullable|email|max:100',
            'account_id' => 'nullable|integer',
            'phone' => 'nullable|string|regex:/^\+?[1-9]\d{1,14}$/',
            'mobile' => 'nullable|string|regex:/^\+?[1-9]\d{1,14}$/',
            'homephone' => 'nullable|string|regex:/^\+?[1-9]\d{1,14}$/',
            'fax' => 'nullable|string|regex:/^\+?[1-9]\d{1,14}$/',
            'title' => 'nullable|string|max:50',
            'department' => 'nullable|string|max:30',
            'description' => 'nullable|string',
            'assistant' => 'nullable|string|max:30',
            'assistantphone' => 'nullable|string|regex:/^\+?[1-9]\d{1,14}$/',
            'birthday' => 'nullable|date',
            'leadsource' => 'nullable|string|max:200',
            'mailingstreet' => 'nullable|string',
            'mailingcity' => 'nullable|string|max:40',
            'mailingstate' => 'nullable|string|max:30',
            'mailingzip' => 'nullable|string|max:30',
            'mailingcountry' => 'nullable|string|max:30',
            'mailingpobox' => 'nullable|string|max:30',
            'otherstreet' => 'nullable|string',
            'othercity' => 'nullable|string|max:40',
            'otherstate' => 'nullable|string|max:30',
            'otherzip' => 'nullable|string|max:30',
            'othercountry' => 'nullable|string|max:30',
            'otherpobox' => 'nullable|string|max:30',
        ]);

        $salutation = $validated['salutation'] ?? $validated['salutationtype'] ?? null;

        // Get module definition for field validation
        $module = $this->moduleRegistry->get('Contacts');

        // Validate file uploads against field configuration
        $this->validateFileUploads($request, $module);

        // Extract fields
        $customFields = [];
        $imagePath = null;

        // Get all custom field definitions to handle empty/cleared fields
        $allCustomFields = $module->fields()->filter(function ($field) {
            return $field->isCustomField();
        });

        // Initialize all custom fields (so we can detect cleared fields)
        foreach ($allCustomFields as $field) {
            $columnName = $field->getColumnName();
            $uitype = $field->getUitype();

            // For multi-select (uitype 33), if not in request, set to empty string
            // For other fields, if not in request, set to null
            if ($uitype == 33) {
                // Multi-select: check if field was submitted
                $customFields[$columnName] = $request->has($columnName) ? $request->input($columnName) : '';
            } else {
                // Other fields: use submitted value or null
                $customFields[$columnName] = $request->input($columnName);
            }
        }

        // Handle file uploads
        foreach ($request->allFiles() as $key => $file) {
            // Handle multiple files
            if (is_array($file)) {
                $paths = [];
                foreach ($file as $singleFile) {
                    $paths[] = $singleFile->store('custom_fields', 'public');
                }
                if (str_starts_with($key, 'cf_')) {
                    $customFields[$key] = json_encode($paths); // Store as JSON array
                }
            } else {
                $path = $file->store('custom_fields', 'public');
                if (str_starts_with($key, 'cf_')) {
                    $customFields[$key] = $path;
                } elseif ($key === 'imagename') {
                    $imagePath = $path;
                }
            }
        }

        // Get existing contact to preserve image if not uploading new one
        $existingContact = $this->contactRepository->findById((int) $id);

        // If no new image uploaded, keep the existing one
        if ($imagePath === null && $existingContact) {
            $imagePath = $existingContact->getImageName();
        }

        $dto = new UpdateContactDTO(array_merge($validated, [
            'lastName' => $validated['lastname'],
            'firstName' => $validated['firstname'] ?? null,
            'salutation' => $salutation,
            'email' => $validated['email'] ?? null,
            'accountId' => $validated['account_id'] ?? null,
            'modifiedBy' => auth('tenant')->id(),
            'image' => $imagePath,
            'customFields' => $customFields,
        ]));


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

    /**
     * Validate file uploads against field configuration
     * 
     * @param Request $request
     * @param \App\Modules\Core\VtigerModules\Domain\ModuleDefinition $module
     * @throws \Illuminate\Validation\ValidationException
     */
    private function validateFileUploads(Request $request, $module): void
    {
        $allFiles = $request->allFiles();

        foreach ($allFiles as $fieldKey => $files) {
            // Normalize to array for consistent processing
            $fileArray = is_array($files) ? $files : [$files];

            // Extract field name (remove cf_ prefix and [] suffix if present)
            $fieldName = str_replace(['cf_', '[]'], '', $fieldKey);

            // Find the field definition
            $field = $module->fields()->first(function ($f) use ($fieldName, $fieldKey) {
                return $f->getFieldName() === $fieldName || $f->getColumnName() === $fieldKey || $f->getColumnName() === str_replace('[]', '', $fieldKey);
            });

            if (!$field) {
                continue; // Skip if field not found
            }

            // Check if field is file/image type (uitype 28 or 69)
            if (!in_array($field->getUitype(), [28, 69])) {
                continue;
            }

            // Get acceptable file types
            $acceptableTypes = $field->getAcceptableFileTypes();

            if (!$acceptableTypes) {
                continue; // No restrictions
            }

            // Parse acceptable types
            $allowedExtensions = array_filter(
                array_map('trim', preg_split('/[\n,]+/', strtolower($acceptableTypes)))
            );

            if (empty($allowedExtensions)) {
                continue;
            }

            // Validate each file
            foreach ($fileArray as $file) {
                if (!$file)
                    continue;

                $extension = strtolower($file->getClientOriginalExtension());

                if (!in_array($extension, $allowedExtensions)) {
                    throw \Illuminate\Validation\ValidationException::withMessages([
                        $fieldKey => __('contacts::contacts.invalid_file_extension', [
                            'extensions' => implode(', ', $allowedExtensions)
                        ]) . ' (File: ' . $file->getClientOriginalName() . ', Extension: ' . $extension . ')'
                    ]);
                }
            }
        }
    }

    /**
     * Delete a file from a contact field
     */
    public function deleteFile(Request $request, $id)
    {
        $field = $request->input('field'); // e.g. 'cf_xxx' or 'imagename'
        $filePath = $request->input('file_path');

        $contact = $this->contactRepository->findById((int) $id);
        if (!$contact) {
            return response()->json(['success' => false, 'message' => 'Contact not found'], 404);
        }

        $currentValue = null;
        $isTableCf = false;

        if (str_starts_with($field, 'cf_')) {
            $currentValue = \DB::connection('tenant')->table('vtiger_contactscf')
                ->where('contactid', $id)
                ->value($field);
            $isTableCf = true;
        } else {
            $currentValue = \DB::connection('tenant')->table('vtiger_contactdetails')
                ->where('contactid', $id)
                ->value($field);
        }

        if (!$currentValue) {
            return response()->json(['success' => false, 'message' => 'Field value not found'], 404);
        }

        // Handle JSON array (multiple files) or single file
        if (str_starts_with($currentValue, '[')) {
            $files = json_decode($currentValue, true);
            if (($key = array_search($filePath, $files)) !== false) {
                unset($files[$key]);
                $newValue = empty($files) ? null : json_encode(array_values($files));
            } else {
                return response()->json(['success' => false, 'message' => 'File not found in field'], 404);
            }
        } else {
            if ($currentValue === $filePath) {
                $newValue = null;
            } else {
                return response()->json(['success' => false, 'message' => 'Path mismatch'], 400);
            }
        }

        // Update database
        if ($isTableCf) {
            \DB::connection('tenant')->table('vtiger_contactscf')
                ->where('contactid', $id)
                ->update([$field => $newValue]);
        } else {
            \DB::connection('tenant')->table('vtiger_contactdetails')
                ->where('contactid', $id)
                ->update([$field => $newValue]);
        }

        // Delete from storage
        if (\Storage::disk('public')->exists($filePath)) {
            \Storage::disk('public')->delete($filePath);
        }

        return response()->json(['success' => true]);
    }
}
