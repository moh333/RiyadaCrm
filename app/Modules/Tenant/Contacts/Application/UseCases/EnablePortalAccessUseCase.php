<?php

namespace App\Modules\Tenant\Contacts\Application\UseCases;

use App\Modules\Tenant\Contacts\Domain\Repositories\ContactRepositoryInterface;
use App\Modules\Tenant\Contacts\Domain\ValueObjects\PortalCredentials;
use App\Modules\Tenant\Contacts\Domain\Events\ContactPortalEnabled;

/**
 * EnablePortalAccessUseCase
 * 
 * Enables customer portal access for a contact.
 * 
 * Business Rules:
 * - Contact must have a valid email address
 * - Portal username = contact's email
 * - System generates random password
 * - Credentials email sent if emailoptout = 0
 * 
 * Database Side-Effects:
 * - Insert/Update vtiger_portalinfo
 * - Update vtiger_customerdetails (set portal = 1)
 * - Send portal credentials email (queued)
 * 
 * Emits: ContactPortalEnabled event
 */
class EnablePortalAccessUseCase
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
     * @return array ['credentials' => PortalCredentials, 'plainPassword' => string]
     * @throws \DomainException if contact not found or has no email
     */
    public function execute(int $contactId): array
    {
        // Find contact
        $contact = $this->contactRepository->findById($contactId);

        if (!$contact) {
            throw new \DomainException("Contact with ID {$contactId} not found");
        }

        if (!$contact->getEmail()) {
            throw new \DomainException("Cannot enable portal access: contact has no email address");
        }

        // Generate random password
        $plainPassword = PortalCredentials::generateRandomPassword();

        // Create portal credentials
        $credentials = PortalCredentials::create(
            $contact->getEmail()->getEmail(),
            $plainPassword
        );

        // Enable portal access on contact
        $contact->enablePortalAccess($credentials);

        // Save to repository
        $this->contactRepository->enablePortalAccess($contactId, $credentials);

        // Determine if email should be sent
        $shouldSendEmail = $contact->getEmail()->canReceiveAutomatedEmails();

        // Emit domain event (listener will send email)
        event(new ContactPortalEnabled($contactId, $credentials, $plainPassword, $shouldSendEmail));

        return [
            'credentials' => $credentials,
            'plainPassword' => $plainPassword,
        ];
    }
}
