<?php

namespace App\Modules\Tenant\Contacts\Domain\Repositories;

use App\Modules\Tenant\Contacts\Domain\CustomField;
use Illuminate\Support\Collection;

/**
 * CustomFieldRepositoryInterface
 * 
 * Repository for managing custom field definitions
 */
interface CustomFieldRepositoryInterface
{
    /**
     * Find all custom fields for a specific module
     * 
     * @param int $tabId Module tab ID (e.g., 4 for Contacts in vtiger)
     * @return Collection<CustomField>
     */
    public function findByModule(int $tabId): Collection;

    /**
     * Find custom field by ID
     */
    public function findById(int $fieldId): ?CustomField;

    /**
     * Find custom field by field name
     */
    public function findByFieldName(int $tabId, string $fieldName): ?CustomField;

    /**
     * Save custom field definition
     */
    public function save(CustomField $field): void;

    /**
     * Delete custom field
     */
    public function delete(int $fieldId): void;

    /**
     * Get next sequence number for a block
     */
    public function getNextSequence(int $blockId): int;

    /**
     * Get next field ID from sequence
     */
    public function nextFieldId(): int;

    /**
     * Check if column name already exists
     */
    public function columnExists(string $columnName, string $tableName): bool;

    /**
     * Create picklist structure
     */
    public function createPicklist(string $fieldName, array $values): void;

    /**
     * Create custom fields table for module if it doesn't exist
     */
    public function ensureCustomTableExists(string $tableName): void;
}
