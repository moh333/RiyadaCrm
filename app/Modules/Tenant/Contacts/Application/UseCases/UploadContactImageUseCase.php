<?php

namespace App\Modules\Tenant\Contacts\Application\UseCases;

use App\Modules\Tenant\Contacts\Domain\Repositories\ContactRepositoryInterface;
use App\Modules\Tenant\Contacts\Domain\Events\ContactImageUploaded;

/**
 * UploadContactImageUseCase
 * 
 * Uploads an image for a contact.
 * 
 * Business Rule: Contact can have at most ONE image (old image deleted)
 * 
 * Database Side-Effects:
 * - Update vtiger_contactdetails.imagename
 * - Insert into vtiger_attachments + vtiger_seattachmentsrel
 * - Delete old image if exists (where setype = 'Contacts Image')
 * 
 * Emits: ContactImageUploaded event
 */
class UploadContactImageUseCase
{
    private ContactRepositoryInterface $contactRepository;

    public function __construct(ContactRepositoryInterface $contactRepository)
    {
        $this->contactRepository = $contactRepository;
    }

    /**
     * Execute the use case
     * 
     * @param int $contactId
     * @param string $imageName
     * @param string $filePath
     * @return void
     * @throws \DomainException if contact not found
     */
    public function execute(int $contactId, string $imageName, string $filePath): void
    {
        // Find contact
        $contact = $this->contactRepository->findById($contactId);

        if (!$contact) {
            throw new \DomainException("Contact with ID {$contactId} not found");
        }

        // Get old image name for cleanup
        $oldImageName = $contact->getImageName();

        // Upload image (repository handles old image deletion)
        $this->contactRepository->uploadImage($contactId, $imageName, $filePath);

        // Update contact entity
        $contact->uploadImage($imageName);

        // Emit domain event
        event(new ContactImageUploaded($contactId, $imageName, $oldImageName ? 1 : null));
    }
}
