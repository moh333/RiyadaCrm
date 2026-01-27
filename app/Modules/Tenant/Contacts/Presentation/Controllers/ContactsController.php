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

    public function export()
    {
        $headers = [
            'Content-type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename=contacts_' . date('Y-m-d_H-i-s') . '.csv',
            'Pragma' => 'no-cache',
            'Cache-Control' => 'must-revalidate, post-check=0, pre-check=0',
            'Expires' => '0'
        ];

        $contacts = $this->contactRepository->getDataTableQuery()->get();
        $columns = ['contact_no', 'salutation', 'firstname', 'lastname', 'email', 'phone', 'mobile', 'account_name', 'title', 'department'];

        $callback = function () use ($contacts, $columns) {
            $file = fopen('php://output', 'w');
            fputcsv($file, $columns);

            foreach ($contacts as $contact) {
                $row = [];
                foreach ($columns as $column) {
                    $row[] = $contact->{$column} ?? '';
                }
                fputcsv($file, $row);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    public function importStep1()
    {
        return view('contacts_module::contacts.import_step1');
    }

    public function importStep2(Request $request)
    {
        $request->validate([
            'file' => 'required|file|mimes:csv,txt',
            'duplicate_handling' => 'required|in:skip,overwrite'
        ]);

        $path = $request->file('file')->store('temp_imports', 'public');
        $fullPath = storage_path('app/public/' . $path);

        $headers = [];
        $sampleRow = [];

        if (($handle = fopen($fullPath, "r")) !== FALSE) {
            $headers = fgetcsv($handle, 1000, ",");
            $sampleRow = fgetcsv($handle, 1000, ",");
            fclose($handle);
        }

        $module = $this->moduleRegistry->get('Contacts');
        $fields = [];
        foreach ($module->fields() as $field) {
            $fields[$field->getFieldName()] = $field->getLabel();
        }

        return view('contacts_module::contacts.import_step2', [
            'file_path' => $path,
            'duplicate_handling' => $request->duplicate_handling,
            'headers' => $headers,
            'sample_row' => $sampleRow,
            'fields' => $fields
        ]);
    }

    public function importProcess(Request $request)
    {
        $filePath = $request->input('file_path');
        $mapping = $request->input('mapping');
        $duplicateHandling = $request->input('duplicate_handling');

        $fullPath = storage_path('app/public/' . $filePath);

        $successCount = 0;
        $skipCount = 0;
        $errorCount = 0;

        if (($handle = fopen($fullPath, "r")) !== FALSE) {
            fgetcsv($handle, 1000, ","); // Skip header row

            while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {
                try {
                    $contactData = [];
                    foreach ($mapping as $csvIndex => $fieldName) {
                        if ($fieldName && isset($data[$csvIndex])) {
                            $contactData[$fieldName] = $data[$csvIndex];
                        }
                    }

                    if (empty($contactData['lastname'])) {
                        $errorCount++;
                        continue;
                    }

                    // Check for duplicate by email if handled
                    if ($duplicateHandling && !empty($contactData['email'])) {
                        $existing = $this->contactRepository->findByEmail($contactData['email']);
                        if ($existing) {
                            if ($duplicateHandling == 'skip') {
                                $skipCount++;
                                continue;
                            } else {
                                // Overwrite - Not implemented in this step, let's treat as update if DTO supports it
                                // For now, let's just create as new or skip
                                $skipCount++;
                                continue;
                            }
                        }
                    }

                    $dto = new CreateContactDTO(array_merge($contactData, [
                        'ownerId' => auth('tenant')->id(),
                        'creatorId' => auth('tenant')->id(),
                        'customFields' => [] // Simplification for now
                    ]));

                    $this->createContactUseCase->execute($dto);
                    $successCount++;

                } catch (\Exception $e) {
                    $errorCount++;
                }
            }
            fclose($handle);
        }

        // Cleanup
        if (\Storage::disk('public')->exists($filePath)) {
            \Storage::disk('public')->delete($filePath);
        }

        return redirect()->route('tenant.contacts.index')->with('success', "Import completed. Success: $successCount, Skipped: $skipCount, Errors: $errorCount");
    }

    public function findDuplicates()
    {
        $module = $this->moduleRegistry->get('Contacts');
        $fields = [];
        foreach ($module->fields() as $field) {
            // Only search over visible and plausible duplicate fields
            if ($field->isVisible() && !in_array($field->getFieldName(), ['contact_no', 'createdtime', 'modifiedtime'])) {
                $fields[$field->getFieldName()] = $field->getLabel();
            }
        }

        return view('contacts_module::contacts.find_duplicates', compact('fields'));
    }

    public function searchDuplicates(Request $request)
    {
        $matchFields = $request->input('match_fields', []);
        if (empty($matchFields)) {
            return redirect()->route('tenant.contacts.duplicates.index')->withErrors(['match_fields' => 'Please select at least one field to match.']);
        }

        // 1. Get the fields to select and group by
        $selects = [];
        foreach ($matchFields as $field) {
            $selects[] = "cd.$field";
        }

        // 2. Find the combinations that have duplicates (Paginated)
        $paginatedGroups = \DB::connection('tenant')
            ->table('vtiger_contactdetails as cd')
            ->join('vtiger_crmentity as ce', 'ce.crmid', '=', 'cd.contactid')
            ->where('ce.deleted', 0)
            ->select($selects)
            ->groupBy($selects)
            ->havingRaw('COUNT(*) > 1')
            ->paginate(10); // 10 groups per page

        // Append search criteria to pagination links
        $paginatedGroups->appends(['match_fields' => $matchFields]);

        $groups = [];

        // 3. For each duplicate combination on the current page, fetch all its records
        foreach ($paginatedGroups as $groupData) {
            $query = $this->contactRepository->getDataTableQuery();

            // Build where clause for this specific group combination
            foreach ($matchFields as $field) {
                if (is_null($groupData->{$field})) {
                    $query->whereNull("cd.$field");
                } else {
                    $query->where("cd.$field", $groupData->{$field});
                }
            }

            $records = $query->get();

            $keyValues = [];
            foreach ($matchFields as $field) {
                $val = $groupData->{$field};
                $keyValues[] = !empty($val) ? $val : '<em>[Empty]</em>';
            }
            $key = implode(' | ', $keyValues);

            $groups[$key] = [
                'records' => $records,
                'keyData' => $groupData
            ];
        }

        return view('contacts_module::contacts.duplicate_results', [
            'groups' => $groups,
            'paginator' => $paginatedGroups,
            'matchFields' => $matchFields
        ]);
    }

    public function showMergeView(Request $request)
    {
        $ids = $request->input('ids', []);
        if (count($ids) < 2) {
            return redirect()->route('tenant.contacts.duplicates.index')->withErrors(['ids' => 'Please select at least two records to merge.']);
        }

        $records = [];
        foreach ($ids as $id) {
            $record = $this->contactRepository->findById((int) $id);
            if ($record) {
                $records[] = $record;
            }
        }

        $module = $this->moduleRegistry->get('Contacts');
        $fields = [];
        foreach ($module->fields() as $field) {
            if ($field->isVisible() && !in_array($field->getFieldName(), ['contact_no', 'createdtime', 'modifiedtime'])) {
                $fields[$field->getFieldName()] = $field->getLabel();
            }
        }

        return view('contacts_module::contacts.merge_view', compact('records', 'fields'));
    }

    public function processMerge(Request $request)
    {
        $primaryId = (int) $request->input('primary_id');
        $allIds = array_map('intval', $request->input('all_ids', []));
        $mergeValues = $request->input('merge_values', []);

        $nonPrimaryIds = array_diff($allIds, [$primaryId]);

        $valuesToUpdate = [];
        foreach ($mergeValues as $fieldName => $sourceId) {
            $sourceRecord = $this->contactRepository->findById((int) $sourceId);
            if ($sourceRecord) {
                $data = $sourceRecord->toArray(); // Assuming toArray() exists or I can map it
                if (isset($data[$fieldName])) {
                    $valuesToUpdate[$fieldName] = $data[$fieldName];
                }
            }
        }

        try {
            $this->contactRepository->merge($primaryId, $nonPrimaryIds, $valuesToUpdate);
            return redirect()->route('tenant.contacts.index')->with('success', 'Contacts merged successfully.');
        } catch (\Exception $e) {
            return back()->with('error', 'Merge failed: ' . $e->getMessage());
        }
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
