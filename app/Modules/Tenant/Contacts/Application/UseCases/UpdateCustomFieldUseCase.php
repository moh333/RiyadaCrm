<?php

namespace App\Modules\Tenant\Contacts\Application\UseCases;

use App\Modules\Tenant\Contacts\Application\DTOs\UpdateCustomFieldDTO;
use App\Modules\Tenant\Contacts\Domain\Repositories\CustomFieldRepositoryInterface;

/**
 * UpdateCustomFieldUseCase
 * 
 * Updates an existing custom field definition
 */
class UpdateCustomFieldUseCase
{
    public function __construct(
        private CustomFieldRepositoryInterface $customFieldRepository
    ) {
    }

    public function execute(UpdateCustomFieldDTO $dto): void
    {
        $field = $this->customFieldRepository->findById($dto->fieldId);

        if (!$field) {
            throw new \DomainException("Custom field with ID {$dto->fieldId} not found");
        }

        // Update metadata
        $field->updateMetadata(
            fieldLabel: $dto->fieldLabel,
            quickCreate: $dto->quickCreate,
            helpInfo: $dto->helpInfo
        );

        $field->setBlock($dto->block);
        $field->setTypeOfData($dto->typeOfData);
        $field->setDefaultValue($dto->defaultValue);

        $this->customFieldRepository->save($field);
    }
}
