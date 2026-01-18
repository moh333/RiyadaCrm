<?php

namespace App\Modules\Tenant\Contacts\Domain\Events;

/**
 * ContactImageUploaded Domain Event
 * 
 * Emitted when a contact image is uploaded.
 * 
 * Business Rule: Contact can have at most ONE image (old image deleted)
 * 
 * Database Side-Effects:
 * - Update vtiger_contactdetails (set imagename)
 * - Insert into vtiger_attachments + vtiger_seattachmentsrel
 * - Delete old image if exists (where setype = 'Contacts Image')
 */
final class ContactImageUploaded
{
    private int $contactId;
    private string $imageName;
    private ?int $oldAttachmentId;
    private \DateTimeImmutable $occurredAt;

    public function __construct(int $contactId, string $imageName, ?int $oldAttachmentId = null)
    {
        $this->contactId = $contactId;
        $this->imageName = $imageName;
        $this->oldAttachmentId = $oldAttachmentId;
        $this->occurredAt = new \DateTimeImmutable();
    }

    public function getContactId(): int
    {
        return $this->contactId;
    }

    public function getImageName(): string
    {
        return $this->imageName;
    }

    public function getOldAttachmentId(): ?int
    {
        return $this->oldAttachmentId;
    }

    public function hasOldImage(): bool
    {
        return $this->oldAttachmentId !== null;
    }

    public function getOccurredAt(): \DateTimeImmutable
    {
        return $this->occurredAt;
    }
}
