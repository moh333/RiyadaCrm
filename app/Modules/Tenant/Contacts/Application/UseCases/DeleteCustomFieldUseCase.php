<?php

namespace App\Modules\Tenant\Contacts\Application\UseCases;

use App\Modules\Tenant\Contacts\Domain\Repositories\CustomFieldRepositoryInterface;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * DeleteCustomFieldUseCase
 * 
 * Deletes a custom field
 * This involves:
 * 1. Soft deleting field definition in vtiger_field
 * 2. Optionally dropping column from vtiger_contactscf (commented out for safety)
 */
class DeleteCustomFieldUseCase
{
    public function __construct(
        private CustomFieldRepositoryInterface $customFieldRepository
    ) {
    }

    public function execute(int $fieldId): void
    {
        $customField = $this->customFieldRepository->findById($fieldId);

        if (!$customField) {
            throw new \DomainException("Custom field not found");
        }

        // 1. Delete the field definition (metadata)
        DB::connection('tenant')->transaction(function () use ($fieldId) {
            $this->customFieldRepository->delete($fieldId);
        });

        // 2. Cleanup picklist if applicable (MUST be outside transaction for drop table)
        if ($customField->getUitype()->hasPicklistValues()) {
            $this->customFieldRepository->deletePicklist($customField->getFieldName());
        }

        // 3. Drop column from custom fields table (MUST be outside transaction for drop column)
        $this->dropColumnFromTable($customField->getTableName(), $customField->getColumnName());
    }

    /**
     * Drop column from custom fields table
     * WARNING: This permanently deletes data!
     */
    private function dropColumnFromTable(string $tableName, string $columnName): void
    {
        if (Schema::connection('tenant')->hasColumn($tableName, $columnName)) {
            Schema::connection('tenant')->table($tableName, function ($table) use ($columnName) {
                $table->dropColumn($columnName);
            });
        }
    }
}
