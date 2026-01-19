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
        \App\Modules\Tenant\Contacts\Application\UseCases\GetModuleCustomFieldsUseCase $getModuleCustomFieldsUseCase
    ) {
        $this->contactRepository = $contactRepository;
        $this->createContactUseCase = $createContactUseCase;
        $this->updateContactUseCase = $updateContactUseCase;
        $this->deleteContactUseCase = $deleteContactUseCase;
        $this->getModuleCustomFieldsUseCase = $getModuleCustomFieldsUseCase;
    }

    public function index(Request $request)
    {
        $contacts = $this->contactRepository->paginate(20, [
            'search' => $request->query('search')
        ]);

        return view('contacts_module::contacts.index', compact('contacts'));

    }

    public function show($id)
    {
        $contact = $this->contactRepository->findById((int) $id);

        if (!$contact) {
            abort(404);
        }

        return view('contacts_module::contacts.show', compact('contact'));

    }

    public function create()
    {
        $customFields = $this->getModuleCustomFieldsUseCase->execute(4); // 4 = Contacts
        return view('contacts_module::contacts.create', compact('customFields'));
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

        // Extract custom fields (cf_*)
        $customFields = [];
        foreach ($request->all() as $key => $value) {
            if (str_starts_with($key, 'cf_')) {
                $customFields[$key] = $value;
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

        $customFields = $this->getModuleCustomFieldsUseCase->execute(4); // 4 = Contacts

        return view('contacts_module::contacts.edit', compact('contact', 'customFields'));
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
        ]);

        // Extract custom fields (cf_*)
        $customFields = [];
        foreach ($request->all() as $key => $value) {
            if (str_starts_with($key, 'cf_')) {
                $customFields[$key] = $value;
            }
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
