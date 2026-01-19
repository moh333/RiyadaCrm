<?php

namespace App\Modules\Tenant\Contacts\Application\UseCases;

use App\Modules\Tenant\Contacts\Domain\Repositories\CustomFieldRepositoryInterface;
use Illuminate\Support\Collection;

/**
 * GetModuleCustomFieldsUseCase
 * 
 * Retrieves all custom field definitions for a specific module
 */
class GetModuleCustomFieldsUseCase
{
    public function __construct(
        private CustomFieldRepositoryInterface $customFieldRepository
    ) {
    }

    /**
     * Execute the use case
     * 
     * @param int $tabId Module tab ID
     * @return Collection
     */
    public function execute(int $tabId): Collection
    {
        return $this->customFieldRepository->findByModule($tabId);
    }
}
