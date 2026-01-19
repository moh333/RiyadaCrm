<?php

namespace App\Modules\Tenant\Contacts\Application\UseCases;

use App\Modules\Tenant\Contacts\Application\DTOs\CreateCustomFieldDTO;
use App\Modules\Tenant\Contacts\Domain\CustomField;
use App\Modules\Tenant\Contacts\Domain\Repositories\CustomFieldRepositoryInterface;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * CreateCustomFieldUseCase
 * 
 * Creates a new custom field for the Contacts module
 * This involves:
 * 1. Creating field definition in vtiger_field
 * 2. Adding column to vtiger_contactscf table
 */
class CreateCustomFieldUseCase
{
    public function __construct(
        private CustomFieldRepositoryInterface $customFieldRepository
    ) {
    }

    public function execute(CreateCustomFieldDTO $dto): CustomField
    {
        // Validate field name is unique
        $existing = $this->customFieldRepository->findByFieldName($dto->tabId, $dto->fieldName);
        if ($existing) {
            throw new \DomainException("Field name '{$dto->fieldName}' already exists");
        }

        // Validate column name doesn't exist
        $columnName = $dto->getColumnName();
        if ($this->customFieldRepository->columnExists($columnName, $dto->getTableName())) {
            // Check if it's a "ghost" column (exists in DB but not in vtiger_field metadata)
            $hasMetadata = DB::connection('tenant')
                ->table('vtiger_field')
                ->where('tablename', $dto->getTableName())
                ->where('columnname', $columnName)
                ->exists();

            if ($hasMetadata) {
                throw new \DomainException("Column '{$columnName}' already exists and is in use.");
            }

            // It's a ghost column from a previous failed deletion/creation - drop it to start fresh
            Schema::connection('tenant')->table($dto->getTableName(), function ($table) use ($columnName) {
                $table->dropColumn($columnName);
            });
        }

        // Create field definition in a transaction
        $customField = DB::connection('tenant')->transaction(function () use ($dto, $columnName) {
            // Get next field ID
            $fieldId = $this->customFieldRepository->nextFieldId();

            // Get next sequence for the block
            $sequence = $this->customFieldRepository->getNextSequence($dto->block);

            // Create custom field definition
            $customField = CustomField::create(
                fieldId: $fieldId,
                tabId: $dto->tabId,
                columnName: $columnName,
                tableName: $dto->getTableName(),
                uitype: $dto->uitype,
                fieldName: $dto->fieldName,
                fieldLabel: $dto->fieldLabel,
                block: $dto->block,
                typeOfData: $dto->typeOfData,
            );

            // Update optional metadata
            $customField->updateMetadata(
                sequence: $sequence,
                quickCreate: $dto->quickCreate,
                helpInfo: $dto->helpInfo,
            );

            // Save field definition
            $this->customFieldRepository->save($customField);

            // If picklist, create the picklist structure
            if ($dto->uitype->hasPicklistValues() && !empty($dto->picklistValues)) {
                $this->customFieldRepository->createPicklist($dto->fieldName, $dto->picklistValues);
            }

            return $customField;
        });

        // Add column to custom fields table AFTER transaction
        try {
            // Ensure the custom table exists for this module
            $this->customFieldRepository->ensureCustomTableExists($dto->getTableName());

            $this->addColumnToTable($dto->getTableName(), $columnName, $dto->uitype);
        } catch (\Throwable $e) {
            // If column creation fails, we should delete the field definition
            // to maintain consistency
            DB::connection('tenant')->transaction(function () use ($customField) {
                $this->customFieldRepository->delete($customField->getFieldId());
            });

            throw new \DomainException(
                "Failed to create column in database: {$e->getMessage()}"
            );
        }

        return $customField;
    }

    /**
     * Add column to custom fields table
     */
    private function addColumnToTable(string $tableName, string $columnName, $uitype): void
    {
        Schema::connection('tenant')->table($tableName, function ($table) use ($columnName, $uitype) {
            $columnType = $uitype->columnType();
            $columnLength = $uitype->columnLength();

            switch ($columnType) {
                case 'string':
                    $table->string($columnName, $columnLength)->nullable();
                    break;
                case 'text':
                    $table->text($columnName)->nullable();
                    break;
                case 'integer':
                    $table->integer($columnName)->nullable();
                    break;
                case 'decimal':
                    $table->decimal($columnName, 10, 2)->nullable();
                    break;
                case 'date':
                    $table->date($columnName)->nullable();
                    break;
                case 'datetime':
                    $table->dateTime($columnName)->nullable();
                    break;
                case 'time':
                    $table->time($columnName)->nullable();
                    break;
                case 'boolean':
                    $table->boolean($columnName)->nullable()->default(false);
                    break;
                default:
                    $table->string($columnName, 255)->nullable();
            }
        });
    }
}
